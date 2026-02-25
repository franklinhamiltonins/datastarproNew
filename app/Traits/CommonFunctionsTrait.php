<?php

namespace App\Traits;

use App\Jobs\CollabMailJob;
use App\Model\AsanaQuestion;
use App\Model\Carrier;
use App\Model\ContactStatus;
use App\Model\Dialing;
use App\Model\Email;
use App\Model\EventLogs;
use App\Model\InsuranceType;
use App\Model\LeadAsanaDetail;
use App\Model\LeadAssignmentLog;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\Message;
use App\Model\Rating;
use App\Model\SmtpConfiguration;
use App\Model\User;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

trait CommonFunctionsTrait
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(($earthRadius * $c), 2);
    }

    /**
     * Remove text after keywords like apartment, unit, apt, #
     */
    public function removeAfterKeywords(string $string): string
    {
        $keywords = ['apartment', 'unit', 'apt', '#'];
        $pattern = '/\b(?:'.implode('|', array_map('preg_quote', $keywords)).')\b.*$/i';

        return preg_replace($pattern, '', $string);
    }

    /**
     * Determine current status based on contact details
     */
    protected function determineCurrentStatus(
        string $cAddress1,
        string $cCity,
        string $cZip,
        string $cEmail
    ): string {
        if (! empty($cAddress1) && ! empty($cCity) &&
            ! empty($cZip) && ! empty($cEmail)) {
            return 'success';
        } elseif (! empty($cAddress1) || ! empty($cCity) ||
                  ! empty($cZip) || ! empty($cEmail)) {
            return 'partial';
        }

        return 'unavailable';
    }

    /**
     * Verify prospect status based on contact details
     */
    public function verifyProspectStatus(
        int $currentPriority,
        int $maxPriority,
        string $oldStatus,
        string $cAddress1,
        string $cCity,
        string $cZip,
        string $cEmail
    ): string {
        $currentStatus = $this->determineCurrentStatus(
            $cAddress1,
            $cCity,
            $cZip,
            $cEmail
        );

        if ($currentStatus == 'success') {
            return 'success';
        }

        return $this->determineFinalStatus(
            $currentPriority,
            $maxPriority,
            $oldStatus,
            $currentStatus
        );
    }

    /**
     * Determine final status based on priority and current status
     */
    protected function determineFinalStatus(
        int $currentPriority,
        int $maxPriority,
        string $oldStatus,
        string $currentStatus
    ): string {
        if ($currentPriority == $maxPriority) {
            if ($oldStatus == 'unavailable' && $currentStatus == 'unavailable') {
                return 'not_found';
            } elseif ($oldStatus == 'partial' || $currentStatus == 'partial') {
                return 'partial_all_platform_performed';
            }
        } elseif ($currentPriority < $maxPriority) {
            if ($oldStatus == 'unavailable' && $currentStatus == 'unavailable') {
                return 'unavailable';
            } elseif ($oldStatus == 'partial' || $currentStatus == 'partial') {
                return 'partial';
            } elseif ($oldStatus == 'pending' || $currentStatus == 'unavailable') {
                return 'unavailable';
            }
        }

        return '';
    }

    /**
     * Generate URL-friendly slug from data array
     */
    public function generateSlug(array $data): string
    {
        $slug = implode('_ ', $data);
        $escapedStr = $this->removeSpecialCharacters($slug);

        return Str::slug(strtolower($escapedStr));
    }

    /**
     * Remove special characters from string
     */
    public function removeSpecialCharacters(string $str): string
    {
        return preg_replace('/[^A-Za-z0-9\s\-]/u', '', $str);
    }

    /**
     * Check if lead slug exists
     */
    public function checkLeadSlugExistence(string $leadSlug): bool
    {
        $leadsQuery = Lead::where('lead_slug', $leadSlug);

        return ! $leadsQuery->exists();
    }

    /**
     * Check if leads exist within distance range
     */
    public function checkLeadsWithinDistance(
        float $newLeadLatitude,
        float $newLeadLongitude,
        float $distanceRange,
        array $existingLeads
    ): array {
        $existanceArr = [];

        foreach ($existingLeads as $existingLead) {
            $distance = $this->calculateDistance(
                $newLeadLatitude,
                $newLeadLongitude,
                $existingLead->latitude,
                $existingLead->longitude
            );

            if ($distance <= $distanceRange) {
                $existanceArr[] = 'Lead '.$existingLead->name.
                    ' already exists within '.$distanceRange.' km range.';
            }
        }

        return $existanceArr;
    }

    /**
     * Build existence check result array
     */
    protected function buildExistenceResult(
        array $existanceArr,
        $existingLeads
    ): array {
        return [
            'status' => 200,
            'message' => $existanceArr,
            'existanceCount' => count($existanceArr),
            'existingLeads' => $existingLeads,
        ];
    }

    /**
     * Check lead slug existence with distance calculation
     */
    public function checkLeadSlugExistanceWithDistance(
        string $leadSlug,
        float $newLeadLatitude,
        float $newLeadLongitude,
        string $id = ''
    ): array {
        $existanceArr = [];
        $distanceRange = 0.1;
        $existingLeads = [];
        $leadsQuery = Lead::where('lead_slug', $leadSlug);

        if ($id) {
            $leadsQuery->where('id', '!=', $id);
        }

        if ($leadsQuery->exists()) {
            $existingLeads = $leadsQuery->get();
            $existanceArr[] = 'Existing slug detected.';

            $distanceMessages = $this->checkLeadsWithinDistance(
                $newLeadLatitude,
                $newLeadLongitude,
                $distanceRange,
                $existingLeads
            );
            $existanceArr = array_merge($existanceArr, $distanceMessages);
        }

        return $this->buildExistenceResult($existanceArr, $existingLeads);
    }

    /**
     * Check contact slug existence
     */
    public function checkContactSlugExistence(string $contactSlug, string $id = ''): array
    {
        $existanceArr = [];
        $existingContacts = [];
        $contactQuery = Contact::where('contact_slug', $contactSlug);

        if ($id) {
            $contactQuery->where('id', '!=', $id);
        }

        if ($contactQuery->exists()) {
            $existingContacts = $contactQuery->get();
            foreach ($existingContacts as $existingContact) {
                $existanceArr[] = 'Contact '.$existingContact->c_full_name.
                    ' already exists.';
            }
        }

        return [
            'status' => 200,
            'message' => $existanceArr,
            'existanceCount' => count($existingContacts),
            'existingContacts' => $existingContacts,
        ];
    }

    /**
     * Check mail configuration for current user
     */
    public function checkMailConfiguration(): int
    {
        $whereCond = [
            ['username', '!=', ''],
            ['password', '!=', ''],
            ['host', '!=', ''],
            ['port', '!=', ''],
            ['encryption', '!=', ''],
            ['from_name', '!=', ''],
        ];

        return SmtpConfiguration::where('user_id', auth()->user()->id)
            ->where($whereCond)
            ->count();
    }

    /**
     * Build SMTP configuration array
     */
    protected function buildSmtpConfig($configuration): array
    {
        $password = Crypt::decryptString("$configuration->password");

        return [
            'driver' => 'smtp',
            'transport' => 'smtp',
            'host' => $configuration->host,
            'port' => $configuration->port,
            'username' => $configuration->username,
            'password' => "$password",
            'encryption' => $configuration->encryption,
            'from' => [
                'address' => $configuration->username,
                'name' => $configuration->from_name,
            ],
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        ];
    }

    /**
     * Set dynamic SMTP configuration for current user
     */
    public function setDynamicSmtp(): void
    {
        $smtpData = $this->checkMailConfiguration();

        if ($smtpData > 0) {
            $configuration = SmtpConfiguration::where('user_id', auth()->user()->id)->first();
            $config = $this->buildSmtpConfig($configuration);
            Config::set('mail', $config);
        }
    }

    /**
     * Save email data
     */
    public function saveEmailData(array $data): void
    {
        $data['user_id'] = auth()->user()->id;
        Email::create($data);
    }

    /**
     * Build search query for relation
     */
    protected function buildRelationSearchQuery(
        Builder $query,
        string $relationName,
        string $relationAttribute,
        string $searchTerm
    ): Builder {
        return $query->orWhereHas($relationName, function (Builder $query)
            use ($relationAttribute, $searchTerm) {
            $query->where(
                $relationAttribute,
                'LIKE',
                "%{$searchTerm}%"
            );
        });
    }

    /**
     * Build search query for attribute
     */
    protected function buildAttributeSearchQuery(
        Builder $query,
        string $attribute,
        string $searchTerm
    ): Builder {
        return $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
    }

    /**
     * Scope a query to search for a term in the attributes
     */
    public function search(Builder $query, $searchTerm, $attributes): Builder
    {
        if (! $searchTerm || ! $attributes) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($attributes, $searchTerm) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(
                    str_contains($attribute, '.') &&
                    method_exists($query->getModel(), explode('.', $attribute)[0]),
                    function (Builder $query) use ($attribute, $searchTerm) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);
                        $this->buildRelationSearchQuery(
                            $query,
                            $relationName,
                            $relationAttribute,
                            $searchTerm
                        );
                    },
                    function (Builder $query) use ($attribute, $searchTerm) {
                        $this->buildAttributeSearchQuery($query, $attribute, $searchTerm);
                    }
                );
            }
        });
    }

    /**
     * Generate secure random number between min and max
     */
    public function generateSecureRandomNumber(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * Fetch arbitrary count for entries
     */
    public function fetchArbitraryCount(int $countOfEntries, int $maxEntriesLoop): int
    {
        $random = $this->generateSecureRandomNumber(2, 95);
        $newNumber = floor((($countOfEntries * $random) / 100));

        if ($newNumber > $maxEntriesLoop) {
            $newNumber = $this->fetchArbitraryCount($newNumber, $maxEntriesLoop);
        }

        return $newNumber ?: 1;
    }

    /**
     * Calculate delay time for key entry
     */
    public function delayTimeCalculation(int $keyEntry): int
    {
        return $keyEntry * $this->generateSecureRandomNumber(30, 150)
            + $this->generateSecureRandomNumber(1, 120);
    }

    /**
     * Calculate delay time for Klaviyo
     */
    public function delayTimeCalculationKlaviyo(int $keyEntry): int
    {
        return $keyEntry * $this->generateSecureRandomNumber(60, 180)
            + $this->generateSecureRandomNumber(1, 120)
            + $this->generateSecureRandomNumber(1, 120);
    }

    /**
     * Remove contact from own lead
     */
    public function removeContactFromOwnLead(int $leadId): void
    {
        DB::table('dialings_leads')
            ->where('lead_id', $leadId)
            ->update([
                'owned_by_agent_id' => 0,
                'status' => 'free',
                'ownmarked_at' => null,
            ]);
    }

    /**
     * Check if log entry should be made
     */
    protected function shouldMakeLogEntry(int $leadId, int $cAgentId, int $statusId): bool
    {
        $logStatus = DB::table('lead_status_wise_log')
            ->where('lead_id', $leadId)
            ->where('status_id', $statusId)
            ->where('agent_id', $cAgentId)
            ->first();

        return ! ($logStatus && empty($logStatus->end_timestamp));
    }

    /**
     * Close existing log entry
     */
    protected function closeExistingLogEntry(int $leadId, int $cAgentId): void
    {
        $estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));

        DB::table('lead_status_wise_log')
            ->where('lead_id', $leadId)
            ->where('agent_id', $cAgentId)
            ->whereNull('end_timestamp')
            ->update([
                'end_timestamp' => $estTime->format('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Create new log entry
     */
    protected function createLogEntry(int $leadId, int $statusId, int $cAgentId): void
    {
        $estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));

        DB::table('lead_status_wise_log')->insert([
            'lead_id' => $leadId,
            'status_id' => $statusId,
            'agent_id' => $cAgentId,
            'start_timestamp' => $estTime->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Make lead status log entry
     */
    public function leadStatusLogMakeEntry(
        int $leadId,
        int $cAgentId,
        int $statusId
    ): void {
        $cAgentId = ! empty($cAgentId) ? $cAgentId : 0;

        if (! $this->shouldMakeLogEntry($leadId, $cAgentId, $statusId)) {
            return;
        }

        $this->closeExistingLogEntry($leadId, $cAgentId);
        $this->createLogEntry($leadId, $statusId, $cAgentId);
    }

    /**
     * Prepare lead collaborators
     */
    private function prepareLeadCollaborators($lead)
    {
        if (! $lead) {
            return collect();
        }

        if (! $lead->relationLoaded('collaborators')) {
            $lead->load(['collaborators' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }]);
        }

        return $lead->collaborators ?? collect();
    }

    /**
     * Prepare TO and CC from collaborator list
     */
    private function prepareToAndCc($collabList): array
    {
        $to = optional($collabList->shift())->email;
        $cc = $collabList->pluck('email')->toArray();

        return [$to, $cc];
    }

    /**
     * Prepare TO and CC from email array
     */
    private function prepareToAndCcArray(array $emails): array
    {
        $to = array_shift($emails);

        return [$to, $emails];
    }

    /**
     * Get agent name by ID
     */
    private function getAgentName(int $id = null): string
    {
        $id = ! empty($id) ? $id : auth()->user()->id;

        return User::where('id', $id)->value('name') ?? 'Unknown Agent';
    }

    /**
     * Get agent email by ID
     */
    private function getAgentEmail(int $id = null): string
    {
        $id = ! empty($id) ? $id : auth()->user()->id;

        return User::where('id', $id)->value('email') ?? 'Unknown Agent';
    }

    /**
     * Prepare contact addition message
     */
    protected function prepareContactAdditionMessage(
        string $agentName,
        string $cFirstName,
        string $cLastName,
        string $newStatusName,
        $lead
    ): string {
        return "{$agentName} has added a new Contact - ({$cFirstName} ".
            "{$cLastName}) of status - {$newStatusName} with Lead - ".
            "{$lead->name} (Lead ID - {$lead->id}).";
    }

    /**
     * Send notification when new contact is added
     */
    public function newContactAdditionWithLeadShoot(
        $lead,
        int $cAgentId,
        string $cFirstName,
        string $cLastName,
        int $newStatusId
    ): void {
        $collabList = $this->prepareLeadCollaborators($lead);

        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $newStatusName = ContactStatus::where('id', $newStatusId)->value('name') ?? 'N/A';
        $agentName = $this->getAgentName($cAgentId);

        $subject = 'New Contact Addition Notification';
        $bodyMsg = $this->prepareContactAdditionMessage(
            $agentName,
            $cFirstName,
            $cLastName,
            $newStatusName,
            $lead
        );

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc);
    }

    /**
     * Prepare contact update message
     */
    protected function prepareContactUpdateMessage(
        string $agentName,
        string $cFirstName,
        string $cLastName,
        string $statusName,
        $lead
    ): string {
        return "{$agentName} has Updated Existing Contact - ({$cFirstName} ".
            "{$cLastName}) status - {$statusName} with Lead - ".
            "{$lead->name} (Lead ID - {$lead->id}).";
    }

    /**
     * Send notification when contact is updated
     */
    public function updateContactShoot(
        $lead,
        int $cAgentId,
        string $cFirstName,
        string $cLastName,
        int $statusId
    ): void {
        $collabList = $this->prepareLeadCollaborators($lead);

        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $statusName = ContactStatus::where('id', $statusId)->value('name') ?? 'N/A';
        $agentName = $this->getAgentName($cAgentId);

        $subject = 'Existing Contact Update Notification';
        $bodyMsg = $this->prepareContactUpdateMessage(
            $agentName,
            $cFirstName,
            $cLastName,
            $statusName,
            $lead
        );

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc);
    }

    /**
     * Prepare assigned list emails
     */
    public function prepareAssignedList(int $assignedUserId, $lead): array
    {
        $emails = collect();

        if ($assignedUserId == -1) {
            $emails = User::role('Service Team', 'Service & Agent')->pluck('email');
        } else {
            $emails->push($this->getAgentEmail($assignedUserId));
        }

        if ($assignedUserId != $lead->pipeline_agent_id) {
            $emails->push($this->getAgentEmail($lead->pipeline_agent_id));
        }

        foreach ($lead->collaborators as $collab) {
            $emails->push($collab->email);
        }

        return $emails->filter()->unique()->values()->toArray();
    }

    /**
     * Prepare assignee message data
     */
    protected function prepareAssigneeMessageData(
        $lead,
        string $agentName,
        string $assignedUserName,
        string $statusName,
        ?string $stageName
    ): array {
        return [
            'leadId' => $lead->id,
            'leadName' => $lead->name,
            'agent_name' => $agentName,
            'assigned_user_name' => $assignedUserName,
            'statusname' => $statusName,
            'stagename' => $stageName,
            'type' => 3,
        ];
    }

    /**
     * Send notification when assignee is added
     */
    public function assigneeAddedMessageShoot(
        string $assignedUserName,
        int $assignedUserId,
        $lead,
        int $screenType
    ): void {
        $emails = $this->prepareAssignedList($assignedUserId, $lead);

        if (empty($emails)) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCcArray($emails);

        $statusName = ContactStatus::where('id', $lead->pipeline_status_id)
            ->value('name') ?? 'N/A';
        $agentName = $this->getAgentName();

        $stageName = null;
        if ($screenType == 2) {
            $stageId = $lead->leadAsanaDetail->asana_stage ?? 1;
            $stageName = AsanaQuestion::where('id', $stageId)->value('name');
        }

        $subject = 'Representative Assignment Notification';
        $data = $this->prepareAssigneeMessageData(
            $lead,
            $agentName,
            $assignedUserName,
            $statusName,
            $stageName
        );

        CollabMailJob::dispatch($subject, '', $to, $cc, $data);
    }

    /**
     * Prepare status change message data
     */
    protected function prepareStatusChangeMessageData(
        $lead,
        string $agentName,
        string $oldStatusName,
        string $newStatusName
    ): array {
        return [
            'leadId' => $lead->id,
            'leadName' => $lead->name,
            'agent_name' => $agentName,
            'old_status_name' => $oldStatusName,
            'new_status_name' => $newStatusName,
            'type' => 1,
        ];
    }

    /**
     * Send notification when status changes
     */
    public function statusChangeMsgShoot(
        $lead,
        int $oldStatusId,
        int $newStatusId
    ): void {
        $collabList = $this->prepareLeadCollaborators($lead);

        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        $oldStatusName = ContactStatus::where('id', $oldStatusId)->value('name');
        $newStatusName = ContactStatus::where('id', $newStatusId)->value('name');
        $agentName = $this->getAgentName();

        $subject = 'Status Changes Notification';
        $bodyMsg = "{$agentName} has changed the status of Lead - {$lead->name} ".
            "(Lead ID - {$lead->id}) from {$oldStatusName} to {$newStatusName}.";
        $data = $this->prepareStatusChangeMessageData(
            $lead,
            $agentName,
            $oldStatusName,
            $newStatusName
        );

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc, $data);
    }

    /**
     * Get stage names for Asana
     */
    protected function getAsanaStageNames(int $newStageId): array
    {
        $oldStageName = null;
        if ($newStageId > 1) {
            $oldStageName = AsanaQuestion::where('id', ($newStageId - 1))->value('name');
        }

        $newStageName = AsanaQuestion::where('id', $newStageId)->value('name');

        return [$oldStageName, $newStageName];
    }

    /**
     * Prepare Asana stage change message data
     */
    protected function prepareAsanaStageMessageData(
        $lead,
        ?string $oldStageName,
        string $newStageName,
        string $agentName
    ): array {
        return [
            'leadId' => $lead->id,
            'leadName' => $lead->name,
            'old_stage_name' => $oldStageName,
            'new_stage_name' => $newStageName,
            'agent_name' => $agentName,
            'type' => 2,
        ];
    }

    /**
     * Send notification when Asana stage changes
     */
    public function asanaStageChangeMsgShoot(int $leadId, int $newStageId): void
    {
        $lead = Lead::find($leadId);
        $collabList = $this->prepareLeadCollaborators($lead);

        if ($collabList->isEmpty()) {
            return;
        }

        [$to, $cc] = $this->prepareToAndCc($collabList);

        [$oldStageName, $newStageName] = $this->getAsanaStageNames($newStageId);
        $agentName = $this->getAgentName();

        $subject = 'Bind Management Stage Changes Notification';
        $bodyMsg = "{$agentName} has changed the Bind Management stage of Lead - ".
            "{$lead->name} (Lead ID - {$lead->id}) to {$newStageName}.";
        $data = $this->prepareAsanaStageMessageData(
            $lead,
            $oldStageName,
            $newStageName,
            $agentName
        );

        CollabMailJob::dispatch($subject, $bodyMsg, $to, $cc, $data);
    }

    /**
     * Fetch referral dialing ID
     */
    public function fetchedReferralDialingId(): int
    {
        $id = 0;
        $dialingQuery = Dialing::select('dialings.id')
            ->where('dialings.referral_marker', 1)
            ->first();

        if ($dialingQuery) {
            $id = $dialingQuery->id;
        }

        return $id;
    }

    /**
     * Assign agent to dialing
     */
    public function assignAgentToDialing(
        int $cAgentId,
        int $dialingId,
        int $type
    ): void {
        if (! empty($cAgentId)) {
            DB::table('dialing_user')
                ->updateOrInsert(
                    ['user_id' => $cAgentId, 'dialing_id' => $dialingId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
        }
    }

    /**
     * Add lead with dialing
     */
    public function addLeadWithDialing(
        int $cAgentId,
        int $dialingId,
        int $leadId
    ): void {
        if (! empty($cAgentId)) {
            $dialingLead = DB::table('dialings_leads')
                ->where('dialing_id', $dialingId)
                ->where('lead_id', $leadId)
                ->where('assigned_to_agent_id', $cAgentId)
                ->first();

            if (! $dialingLead) {
                DB::table('dialings_leads')->insert([
                    'assigned_to_agent_id' => $cAgentId,
                    'dialing_id' => $dialingId,
                    'lead_id' => $leadId,
                    'ownmarked_at' => null,
                ]);
            }
        }
    }

    /**
     * Get contact with highest priority status
     */
    protected function getHighestPriorityContact(int $leadId)
    {
        return Contact::join(
            'contact_status',
            'contacts.c_status',
            '=',
            'contact_status.id'
        )
            ->where('contacts.lead_id', $leadId)
            ->orderBy('contact_status.priority', 'desc')
            ->select('contacts.c_status', 'contacts.c_agent_id')
            ->first();
    }

    /**
     * Update lead pipeline from contact
     */
    protected function updateLeadPipelineFromContact(
        Lead $lead,
        int $contactStatus,
        int $contactAgentId
    ): void {
        $oldStatusId = $lead->pipeline_status_id;
        $lead->pipeline_status_id = $contactStatus;
        $lead->pipeline_agent_id = $contactAgentId;
        $lead->save();

        $newStatusId = $lead->pipeline_status_id;

        if ($oldStatusId != $newStatusId) {
            $this->statusChangeMsgShoot($lead, $oldStatusId, $newStatusId);
        }
    }

    /**
     * Handle special contact status update
     */
    protected function handleSpecialContactStatusUpdate(int $leadId): void
    {
        $contactStatusId = ContactStatus::whereIn('special_marker', [1, 2])
            ->where('id', function ($query) use ($leadId) {
                $query->select('c_status')
                    ->from('contacts')
                    ->where('lead_id', $leadId)
                    ->orderBy('c_status', 'desc')
                    ->limit(1);
            })
            ->first();

        if ($contactStatusId) {
            $contactNot = $this->getNextContactStatus($leadId);

            if ($contactNot) {
                Lead::where('id', $leadId)->update([
                    'pipeline_status_id' => $contactNot->c_status,
                    'pipeline_agent_id' => $contactNot->c_agent_id,
                ]);
            }
        }
    }

    /**
     * Update lead status based on contact
     */
    public function contactBasedLeadStatusUpdate(
        int $leadId,
        int $agentId,
        int $statusId
    ): bool {
        $contact = $this->getHighestPriorityContact($leadId);

        if ($contact) {
            $lead = Lead::where('id', $leadId)->first();

            if ($lead) {
                $this->updateLeadPipelineFromContact(
                    $lead,
                    $contact->c_status,
                    $contact->c_agent_id
                );
                $this->leadStatusLogMakeEntry($leadId, $contact->c_agent_id, $statusId);
            }

            $this->handleSpecialContactStatusUpdate($leadId);
        } else {
            Lead::where('id', $leadId)->update([
                'pipeline_status_id' => null,
                'pipeline_agent_id' => null,
            ]);
        }

        return true;
    }

    /**
     * Get next contact status for lead
     */
    protected function getNextContactStatus(int $leadId)
    {
        return Contact::join(
            'contact_status',
            'contacts.c_status',
            '=',
            'contact_status.id'
        )
            ->where('contacts.lead_id', $leadId)
            ->where(function ($query) {
                $query->whereNotIn('contact_status.special_marker', [1, 2])
                    ->orWhereNull('contact_status.special_marker');
            })
            ->orderBy('contact_status.priority', 'desc')
            ->select('contacts.c_status', 'contacts.c_agent_id')
            ->first();
    }

    /**
     * Find or get insurance type
     */
    protected function findInsuranceType(string $type): ?InsuranceType
    {
        return InsuranceType::where('status', 1)
            ->where('name', $type)
            ->first();
    }

    /**
     * Create new carrier entry
     */
    protected function createCarrierEntry(string $value, InsuranceType $insuranceType): int
    {
        $carrier = Carrier::create([
            'name' => trim($value),
            'status' => 2,
        ]);
        $carrier->insuranceTypes()->attach([$insuranceType->id]);

        return $carrier->id;
    }

    /**
     * Link insurance type to carrier
     */
    protected function linkInsuranceTypeToCarrier(
        Carrier $carrier,
        int $insuranceTypeId
    ): void {
        $existingLinkedTypes = $carrier->insuranceTypes()
            ->pluck('insurance_type_id')
            ->toArray();

        if (! in_array($insuranceTypeId, $existingLinkedTypes)) {
            $carrier->insuranceTypes()->attach([$insuranceTypeId]);
        }
    }

    /**
     * Make log in carrier table
     */
    public function makeLogInCarrierTable(string $value, string $type): ?int
    {
        $returnId = null;

        if (! empty($value)) {
            $insuranceType = $this->findInsuranceType($type);

            if ($insuranceType) {
                $alreadyEntry = Carrier::where('name', trim($value))
                    ->whereIn('status', [1, 2])
                    ->first();

                if (! $alreadyEntry) {
                    $returnId = $this->createCarrierEntry($value, $insuranceType);
                } else {
                    $returnId = $alreadyEntry->id;
                    $this->linkInsuranceTypeToCarrier(
                        $alreadyEntry,
                        $insuranceType->id
                    );
                }
            }
        }

        return $returnId;
    }

    /**
     * Create new rating entry
     */
    protected function createRatingEntry(string $value, InsuranceType $insuranceType): int
    {
        $rating = Rating::create([
            'name' => trim($value),
            'status' => 2,
        ]);
        $rating->insuranceTypes()->sync([$insuranceType->id]);

        return $rating->id;
    }

    /**
     * Link insurance type to rating
     */
    protected function linkInsuranceTypeToRating(
        Rating $rating,
        int $insuranceTypeId
    ): void {
        $existingLinkedTypes = $rating->insuranceTypes()
            ->pluck('insurance_type_id')
            ->toArray();

        if (! in_array($insuranceTypeId, $existingLinkedTypes)) {
            $rating->insuranceTypes()->attach([$insuranceTypeId]);
        }
    }

    /**
     * Make log in rating table
     */
    public function makeLogInRatingTable(string $value, string $type): ?int
    {
        $returnId = null;

        if (! empty($value)) {
            $insuranceType = $this->findInsuranceType($type);

            if ($insuranceType) {
                $alreadyEntry = Rating::where('name', trim($value))
                    ->whereIn('status', [1, 2])
                    ->first();

                if (! $alreadyEntry) {
                    $returnId = $this->createRatingEntry($value, $insuranceType);
                } else {
                    $returnId = $alreadyEntry->id;
                    $this->linkInsuranceTypeToRating(
                        $alreadyEntry,
                        $insuranceType->id
                    );
                }
            }
        }

        return $returnId;
    }

    /**
     * Clear lead Asana detail table
     */
    public function clearLeadAsanaDetailTable(int $tableId): void
    {
        $leadAsanaDetail = LeadAsanaDetail::find($tableId);

        if ($leadAsanaDetail) {
            $this->resetAsanaDetailFields($leadAsanaDetail);
            $leadAsanaDetail->save();
        }
    }

    /**
     * Get Asana detail string fields
     */
    protected function getAsanaDetailStringFields(): array
    {
        return [
            'appraisal', 'wind_mitigation', 'loss_run_authorization',
            'inspection_contact_form', 'sov_form', 'accord_form',
            'property_service_send_market', 'general_liability_service_send_market',
            'do_service_send_market', 'legal_defense_service_send_market',
            'umbrella_service_send_market', 'crime_service_send_market',
            'workers_comp_service_send_market', 'flood_service_send_market',
            'sent_to_client', 'meeting_with_client', 'signed_docusign_received',
            'property_bind_coverage', 'general_liability_bind_coverage',
            'do_bind_coverage', 'legal_defense_bind_coverage',
            'umbrella_bind_coverage', 'crime_bind_coverage',
            'workers_comp_bind_coverage', 'flood_bind_coverage',
            'add_policies_to_epic', 'send_invoices_to_accounting',
            'add_policies_to_bind_document', 'add_policies_to_eoi_direct',
            'send_policies_to_insured', 'down_payment', 'financing',
            'property_payment', 'general_liability_payment', 'do_payment',
            'legal_defense_payment', 'umbrella_payment', 'crime_payment',
            'workers_comp_payment', 'flood_payment',
            'property_service_send_market_list',
            'general_liability_service_send_market_list',
            'do_service_send_market_list',
            'legal_defense_service_send_market_list',
            'umbrella_service_send_market_list',
            'crime_service_send_market_list',
            'workers_comp_service_send_market_list',
            'flood_service_send_market_list',
            'property_bind_coverage_list',
            'general_liability_bind_coverage_list',
            'do_bind_coverage_list', 'legal_defense_bind_coverage_list',
            'umbrella_bind_coverage_list', 'crime_bind_coverage_list',
            'workers_comp_bind_coverage_list', 'flood_bind_coverage_list',
            'property_payment_list', 'general_liability_payment_list',
            'do_payment_list', 'legal_defense_payment_list',
            'umbrella_payment_list', 'crime_payment_list',
            'workers_comp_payment_list', 'flood_payment_list',
        ];
    }

    /**
     * Reset Asana detail fields
     */
    protected function resetAsanaDetailFields($leadAsanaDetail): void
    {
        $stringFields = $this->getAsanaDetailStringFields();

        foreach ($stringFields as $field) {
            $leadAsanaDetail->$field = '';
        }

        $leadAsanaDetail->asana_stage = 1;
        $leadAsanaDetail->stage_completed = 0;
        $leadAsanaDetail->renewed_lead = 1;
    }

    /**
     * Clear lead detail table
     */
    public function clearLeadDetailTable(int $tableId): void
    {
        $lead = Lead::find($tableId);

        if ($lead) {
            $this->resetLeadFields($lead);
            $lead->save();
        }
    }

    /**
     * Get lead basic fields to reset
     */
    protected function getLeadBasicFields(): array
    {
        return [
            'renewal_date' => null,
            'renewal_month' => '',
            'premium' => null,
            'premium_year' => null,
            'insured_amount' => null,
            'insured_year' => null,
            'current_agency' => null,
            'current_agent' => null,
            'ins_prop_carrier' => null,
            'renewal_carrier_month' => null,
            'ins_flood' => 'NO',
            'prop_floor' => null,
            'roof_geom' => null,
            'roof_covering' => null,
        ];
    }

    /**
     * Get lead insurance fields to reset
     */
    protected function getLeadInsuranceFields(): array
    {
        return [
            'general_liability' => null,
            'GL_ren_month' => null,
            'crime_insurance' => null,
            'CI_ren_month' => null,
            'directors_officers' => null,
            'DO_ren_month' => null,
            'umbrella' => null,
            'U_ren_month' => null,
            'workers_compensation' => null,
            'WC_ren_month' => null,
            'flood' => null,
            'F_ren_month' => null,
            'roof_connection' => null,
            'roof_year' => null,
            'other_community_info' => null,
            'iso' => null,
            'policy_renewal_date' => null,
            'wind_mitigation_date' => null,
        ];
    }

    /**
     * Get lead rating fields to reset
     */
    protected function getLeadRatingFields(): array
    {
        return [
            'gl_expiry_premium' => '',
            'gl_policy_renewal_date' => null,
            'ci_expiry_premium' => null,
            'ci_policy_renewal_date' => null,
            'ci_rating' => '',
            'employee_theft' => '',
            'operating_reserves' => '',
            'pending_litigation' => '',
            'litigation_date' => null,
            'do_expiry_premium' => null,
            'do_policy_renewal_date' => null,
            'do_rating' => '',
            'claims_made' => '',
            'umbrella_expiry_premium' => null,
            'umbrella_policy_renewal_date' => null,
            'umbrella_rating' => '',
            'umbrella_exclusions' => '',
            'umbrella_other_exclusions' => '',
        ];
    }

    /**
     * Get lead worker comp fields to reset
     */
    protected function getLeadWorkerCompFields(): array
    {
        return [
            'wc_expiry_premium' => null,
            'wc_policy_renewal_date' => null,
            'wc_rating' => '',
            'employee_count' => '',
            'employee_payroll' => '',
        ];
    }

    /**
     * Get lead flood fields to reset
     */
    protected function getLeadFloodFields(): array
    {
        return [
            'flood_expiry_premium' => null,
            'flood_policy_renewal_date' => null,
            'flood_rating' => '',
            'elevation_certificate' => '',
            'loma_letter' => '',
        ];
    }

    /**
     * Get lead coverage fields to reset
     */
    protected function getLeadCoverageFields(): array
    {
        return [
            'gl_insurance_coverage' => null,
            'ci_insurance_coverage' => null,
            'do_insurance_coverage' => null,
            'u_insurance_coverage' => null,
            'wc_insurance_coverage' => null,
            'difference_in_condition' => null,
            'dic_ren_month' => null,
            'dic_expiry_premium' => null,
            'dic_policy_renewal_date' => null,
            'dic_hurricane_deductible' => null,
            'dic_all_other_perils' => null,
            'dic_insurance_coverage' => null,
        ];
    }

    /**
     * Get lead X-wind fields to reset
     */
    protected function getLeadXWindFields(): array
    {
        return [
            'x_wind' => null,
            'xw_ren_month' => null,
            'xw_expiry_premium' => null,
            'xw_policy_renewal_date' => null,
            'xw_hurricane_deductible' => null,
            'xw_all_other_perils' => null,
            'xw_insurance_coverage' => null,
        ];
    }

    /**
     * Get lead equipment breakdown fields to reset
     */
    protected function getLeadEquipmentBreakdownFields(): array
    {
        return [
            'equipment_breakdown' => null,
            'eb_ren_month' => null,
            'eb_expiry_premium' => null,
            'eb_policy_renewal_date' => null,
            'eb_hurricane_deductible' => null,
            'eb_all_other_perils' => null,
            'eb_insurance_coverage' => null,
        ];
    }

    /**
     * Get lead commercial auto fields to reset
     */
    protected function getLeadCommercialAutoFields(): array
    {
        return [
            'commercial_automobiles' => null,
            'ca_ren_month' => null,
            'ca_expiry_premium' => null,
            'ca_policy_renewal_date' => null,
            'ca_hurricane_deductible' => null,
            'ca_all_other_perils' => null,
            'ca_insurance_coverage' => null,
        ];
    }

    /**
     * Get lead marina fields to reset
     */
    protected function getLeadMarinaFields(): array
    {
        return [
            'marina' => null,
            'm_ren_month' => null,
            'm_expiry_premium' => null,
            'm_policy_renewal_date' => null,
            'm_hurricane_deductible' => null,
            'm_all_other_perils' => null,
            'm_insurance_coverage' => null,
        ];
    }

    /**
     * Reset lead fields
     */
    protected function resetLeadFields($lead): void
    {
        $leadFields = array_merge(
            $this->getLeadBasicFields(),
            $this->getLeadInsuranceFields(),
            $this->getLeadRatingFields(),
            $this->getLeadWorkerCompFields(),
            $this->getLeadFloodFields(),
            $this->getLeadCoverageFields(),
            $this->getLeadXWindFields(),
            $this->getLeadEquipmentBreakdownFields(),
            $this->getLeadCommercialAutoFields(),
            $this->getLeadMarinaFields()
        );

        foreach ($leadFields as $field => $value) {
            $lead->$field = $value;
        }
    }

    /**
     * Main insurance carrier mapping
     */
    public $mainInsuranceCarrier = [
        'Property' => 'ins_prop_carrier',
        'General Liability' => 'general_liability',
        'Crime Insurance' => 'crime_insurance',
        'Directors & Officers' => 'directors_officers',
        'Umbrella' => 'umbrella',
        'Workers Compensation' => 'workers_compensation',
        'Flood' => 'flood',
        'Difference In Conditions' => 'difference_in_condition',
        'X-Wind' => 'x_wind',
        'Equipment Breakdown' => 'equipment_breakdown',
        'Commercial AutoMobile' => 'commercial_automobiles',
        'Marina' => 'marina',
    ];

    /**
     * Additional policies carrier mapping
     */
    public $additionalPoliciesCarrier = [
        'Glass' => 'glass',
        'Excess Directors & Officers' => 'excess_directors_officers',
        'Excess Liability' => 'excess_liability',
        'Pollution' => 'pollution',
        'Commercial' => 'commercial',
        'Legal Defense' => 'legal_defence',
        'Cyber' => 'cyber',
    ];

    /**
     * Main insurance rating mapping
     */
    public $mainInsuranceRating = [
        'Property' => 'rating',
        'General Liability' => 'gl_rating',
        'Crime Insurance' => 'ci_rating',
        'Directors & Officers' => 'do_rating',
        'Umbrella' => 'umbrella_rating',
        'Workers Compensation' => 'wc_rating',
        'Flood' => 'flood_rating',
    ];

    /**
     * Get agent listing based on user role and permissions
     */
    public function getAgentListing(
        bool $isAdminUser,
        int $agentId,
        bool $everyone = true,
        array $roles = []
    ): array {
        $agentUsers = [];

        if (in_array('Manager', $roles, true)) {
            $agentUsers = $this->getManagerTeamAgents($agentId);
        } else {
            if ($isAdminUser) {
                $agentUsers = $this->getAllAgents($everyone);
            } else {
                $agentUsers = $this->getUserAndAccessibleAgents($agentId);
            }
        }

        return $agentUsers;
    }

    /**
     * Format agent data for listing
     */
    protected function formatAgentData(User $agent): array
    {
        return [
            'displayname' => "{$agent->name} ({$agent->email})",
            'name' => $agent->name,
            'email' => $agent->email,
            'id' => $agent->id,
        ];
    }

    /**
     * Get manager team agents
     */
    protected function getManagerTeamAgents(int $agentId): array
    {
        $agentUsers = [];
        $user = User::where('id', $agentId)->first();

        if ($user) {
            $agentUsers[$agentId] = [
                'displayname' => "{$user->name} ({$user->email})",
                'name' => $user->name,
                'email' => $user->email,
                'id' => $user->id,
            ];

            $agents = $user->managerTeamList;
            foreach ($agents as $agent) {
                $agentUsers[$agent->id] = $this->formatAgentData($agent);
            }
        }

        return $agentUsers;
    }

    /**
     * Get all agents
     */
    protected function getAllAgents(bool $everyone): array
    {
        $agentUsers = [];
        $agents = User::role(['Agent', 'Service & Agent'])->get();

        if ($everyone) {
            $agentUsers[0] = [
                'displayname' => 'Everyone',
                'name' => 'Everyone',
                'email' => 'Everyone',
                'id' => 0,
            ];
        }

        foreach ($agents as $agent) {
            $agentUsers[$agent->id] = $this->formatAgentData($agent);
        }

        return $agentUsers;
    }

    /**
     * Get user and accessible agents
     */
    protected function getUserAndAccessibleAgents(int $agentId): array
    {
        $agentUsers = [];
        $user = User::where('id', $agentId)->first();

        if ($user) {
            $agentUsers[$agentId] = [
                'displayname' => "{$user->name} ({$user->email})",
                'name' => $user->name,
                'email' => $user->email,
                'id' => $user->id,
            ];

            $agents = $user->accessibleUsers;
            foreach ($agents as $agent) {
                $agentUsers[$agent->id] = $this->formatAgentData($agent);
            }
        }

        return $agentUsers;
    }

    /**
     * Update dialing lists
     */
    public function updateDialingLists(
        int $statusId,
        int $contactId,
        int $leadId,
        int $cAgentId = 0,
        int $ownStatus = null
    ): void {
        $leadDialingId = $this->getLeadDialingId($leadId, $cAgentId);

        if (empty($leadDialingId)) {
            $referralDialingId = $this->fetchedReferralDialingId();
            $this->assignAgentToDialing($cAgentId, $referralDialingId, 1);
            $this->addLeadWithDialing($cAgentId, $referralDialingId, $leadId);
            $leadDialingId = $referralDialingId;
        }

        if (empty($cAgentId)) {
            $cAgentId = auth()->user()->id;
        }

        $this->processDialingUpdate($leadId, $leadDialingId, $cAgentId);
    }

    /**
     * Get lead dialing ID
     */
    protected function getLeadDialingId(int $leadId, int $cAgentId): ?int
    {
        return DB::table('dialings_leads')
            ->where('lead_id', $leadId)
            ->where('assigned_to_agent_id', $cAgentId)
            ->orderByDesc('dialing_id')
            ->value('dialing_id');
    }

    /**
     * Get own contact status IDs
     */
    protected function getOwnContactStatusIds(): array
    {
        return ContactStatus::where('status_type', 2)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Check if lead has own contact
     */
    protected function hasOwnContact(int $leadId, array $ownStatusArray): bool
    {
        return Contact::where('lead_id', $leadId)
            ->whereNotNull('c_phone')
            ->whereIn('c_status', $ownStatusArray)
            ->exists();
    }

    /**
     * Build dialing update array
     */
    protected function buildDialingUpdateArray(bool $hasOwnContact, int $cAgentId): array
    {
        $updateArr = [
            'owned_by_agent_id' => 0,
            'status' => 'free',
            'ownmarked_at' => null,
        ];

        if ($hasOwnContact) {
            $estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
            $estTimeNow = $estTime->format('Y-m-d H:i:s');
            $updateArr = [
                'owned_by_agent_id' => $cAgentId,
                'status' => 'own',
                'ownmarked_at' => $estTimeNow,
            ];
        }

        return $updateArr;
    }

    /**
     * Process dialing update
     */
    protected function processDialingUpdate(
        int $leadId,
        int $leadDialingId,
        int $cAgentId
    ): void {
        $ownStatusArray = $this->getOwnContactStatusIds();
        $hasOwnContact = $this->hasOwnContact($leadId, $ownStatusArray);
        $updateArr = $this->buildDialingUpdateArray($hasOwnContact, $cAgentId);

        if ($hasOwnContact) {
            DB::table('dialings_leads')
                ->where('lead_id', $leadId)
                ->update([
                    'owned_by_agent_id' => 0,
                    'status' => 'free',
                    'ownmarked_at' => null,
                ]);
        }

        DB::table('dialings_leads')
            ->where('dialing_id', $leadDialingId)
            ->where('lead_id', $leadId)
            ->where('assigned_to_agent_id', $cAgentId)
            ->update($updateArr);
    }

    /**
     * Set contact to queue
     */
    public function setContactToQueue($lead): void
    {
        $contactCount = Contact::where('lead_id', $lead->id)
            ->whereNotNull('c_phone')
            ->where('c_status', 'Select Status')
            ->count();

        $lead->no_of_times_contacts_called = $lead->no_of_times_contacts_called + 1;

        if ($contactCount == 0) {
            $lead->queued_at = now();
        }

        $lead->save();
    }

    /**
     * Get pipedrive display status list
     */
    public function pipeDriveDisplayStatusList()
    {
        return ContactStatus::where('false_status', 0)
            ->whereNotNull('display_in_pipedrive')
            ->pluck('id');
    }

    /**
     * Decide color tile for lead
     */
    public function decideColorTile(
        int $agentId,
        int $notifyDays,
        $estTime,
        $nowTime,
        int $statusId,
        int $leadId
    ): int {
        $displayTileColor = 1;

        $itemBacklog = DB::table('lead_status_wise_log')
            ->where('lead_id', $leadId)
            ->where('status_id', $statusId)
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('agent_id', $agentId);
            })
            ->where('start_timestamp', '<', $estTime)
            ->exists();

        if ($itemBacklog) {
            $displayTileColor = 2;

            $hasRecentEvent = EventLogs::where('lead_id', $leadId)
                ->where('event_date', '>=', $nowTime->toDateString())
                ->exists();

            if ($hasRecentEvent) {
                $displayTileColor = 3;
            }
        }

        return $displayTileColor;
    }

    /**
     * Get signed AOR status ID
     */
    public function getSignedAorStatusId(): int
    {
        $contact = ContactStatus::select('id', 'name')
            ->where('special_marker', 3)
            ->first();

        return $contact->id ?? 0;
    }

    /**
     * Calculate lead total premium
     */
    public function leadTotalPremiumCalculation(int $leadId)
    {
        $lead = Lead::find($leadId);
        $additionalPolicy = $lead->leadAdditionalpolicy()->get();

        return totalPremiumCalculationLeadWise($lead, $additionalPolicy);
    }

    /**
     * Update lead total premium
     */
    public function leadTotalPremiumUpdate(int $leadId): void
    {
        $totalPremium = $this->leadTotalPremiumCalculation($leadId);

        Lead::where('id', $leadId)->update(['total_premium' => $totalPremium]);
    }

    /**
     * Make log for assignee agent
     */
    public function makeLogForAssigneeAgent(int $assignedUserId, $lead): void
    {
        LeadAssignmentLog::create([
            'status_id' => $lead->pipeline_status_id,
            'agent_id' => $lead->pipeline_agent_id,
            'assigned_user_id' => $assignedUserId,
            'lead_id' => $lead->id,
            'changed_by_user_id' => auth()->id(),
        ]);
    }

    /**
     * Build message query for max execution time check
     */
    protected function buildMessageQuery(string $isNewsletter, int $chatContactId): \Illuminate\Database\Eloquent\Builder
    {
        $checkLastMsg = Message::where('chat_type', 'outbound')
            ->where('chat_sms_sent_status', '1');

        if (strtolower($isNewsletter) != 'yes') {
            $checkLastMsg = $checkLastMsg->where('contact_id', $chatContactId);
        } else {
            $checkLastMsg = $checkLastMsg->where('newsletter_id', $chatContactId);
        }

        return $checkLastMsg->orderBy('created_at', 'desc');
    }

    /**
     * Calculate time difference for message
     */
    protected function calculateTimeDifference($checkLastMsg): ?array
    {
        if (isset($checkLastMsg->max_time_to_send)) {
            $maxTimeToSend = Carbon::parse($checkLastMsg->max_time_to_send);
            $nowAt = Carbon::now();
            $timeDifferenceInMinutes = $nowAt->diffInMinutes($maxTimeToSend, false);

            return [
                'success' => true,
                'status' => 200,
                'response' => $timeDifferenceInMinutes,
                'timeleft' => $timeDifferenceInMinutes,
            ];
        }

        return null;
    }

    /**
     * Check max execution time
     */
    public function checkMaxExecutionTime(int $chatContactId, string $isNewsletter = '')
    {
        $checkLastMsg = $this->buildMessageQuery($isNewsletter, $chatContactId)
            ->first();

        $timeResult = $this->calculateTimeDifference($checkLastMsg);

        if ($timeResult) {
            return response()->json($timeResult);
        }

        return response()->json([
            'success' => false,
            'status' => 404,
            'response' => 0,
        ]);
    }

    /**
     * Create template slug
     */
    public function createTemplateSlug(array $data): string
    {
        return Str::slug($data['template_name'], '-').'-'.
            auth()->user()->id.'-'.$data['template_type'];
    }

    /**
     * Check if user is admin
     */
    public function checkingIsAdminUser($user): bool
    {
        return $user->can('agent-create') ||
               $user->can('all-accounts-list-pipedrive');
    }

    /**
     * Check if user can display all accounts
     */
    public function checkingIsAllAccountDisplay($user, bool $isAdminUser): bool
    {
        return $isAdminUser || $user->hasRole('Manager');
    }

    /**
     * Get manager ID from user
     */
    public function getManagerId($user): int
    {
        if ($user->hasRole('Manager')) {
            return $user->id;
        }

        return 0;
    }
}
