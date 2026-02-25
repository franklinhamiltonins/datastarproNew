<?php

namespace App\Traits;

use App\Model\ActivityReport;
use App\Model\ActivityReportAor;
use App\Model\ActivityReportFile;
use App\Model\AgentLog;
use App\Model\ContactStatus;
use App\Model\DailyCallReportLog;
use App\Model\LeadSource;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Note;
use App\Model\MailerLeadTracker;
use App\Model\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

trait ActivityReportTrait
{
    /**
     * Check if all required fields are present to create a lead entry
     */
    protected function checkCanMakeEntryLead(array $data): bool
    {
        $requiredFields = [
            'business',
            'business_type',
            'business_address',
            'business_city',
            'business_zip',
            'contact_firstname',
            'contact_lastname',
            'contact_address',
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create lead with mailer data
     */
    protected function createLeadWithMailerData(array $data, int $trackerId): array
    {
        $response = [
            'error' => false,
            'msg' => '',
        ];

        try {
            DB::beginTransaction();

            // Get latitude & longitude from address
            $latLong = $this->getLatLongFromAddress($data);
            $lat = $latLong['lat'] ?? null;
            $long = $latLong['long'] ?? null;

            // Generate lead and check for duplication
            $lead = $this->createLeadFromMailerData($data, $lat, $long);

            // Generate contact and check for duplication
            $contact = $this->createContactFromMailerData($data, $lead);

            // Update contact status and lead
            $this->updateLeadStatusFromContact($lead, $contact, $data);

            // Create note if status note is provided
            $this->createStatusNoteIfProvided($data, $lead, $contact);

            // Update tracker status
            MailerLeadTracker::where('id', $trackerId)->update([
                'status' => 1,
                'lead_id' => $lead->id,
            ]);

            DB::commit();
            $response['msg'] = 'Lead created successfully with the given Information';
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['msg'] = $e->getMessage();
            DB::rollBack();
        }

        return $response;
    }

    /**
     * Get latitude and longitude from address
     */
    protected function getLatLongFromAddress(array $data): array
    {
        $addressParts = array_filter([
            $data['business_address'] ?? '',
            $data['business_city'] ?? '',
            $data['business_zip'] ?? '',
        ]);

        $newAddress = implode(', ', $addressParts);
        $latLong = $this->getLatLngFromGoogle($newAddress);

        return $latLong;
    }

    /**
     * Create lead from mailer data
     */
    protected function createLeadFromMailerData(
        array $data,
        ?float $lat,
        ?float $long
    ): Lead {
        $leadSlug = $this->generateLeadSlug($data);
        $name = $this->removeSpecialCharacters($data['business']);
        $slugExistance = $this->checkLeadSlugExistanceWithDistance(
            $leadSlug,
            $lat,
            $long
        );

        if (! empty($slugExistance['existanceCount'])) {
            throw new \InvalidArgumentException(
                implode('<br>', $slugExistance['message'])
            );
        }

        $lead = Lead::create([
            'type' => $data['business_type'],
            'name' => $name,
            'address1' => $data['business_address'],
            'city' => $data['business_city'],
            'zip' => $data['business_zip'],
            'latitude' => $lat,
            'longitude' => $long,
            'lead_slug' => $leadSlug,
            'lead_source' => $data['lead_source'],
        ]);

        create_log($lead, 'Create Lead', '');

        return $lead;
    }

    /**
     * Generate unique lead slug
     */
    protected function generateLeadSlug(array $data): string
    {
        return $this->generateSlug([
            $data['business_type'],
            $data['business'],
            $data['business_city'],
            $data['business_zip'],
        ]);
    }

    /**
     * Create contact from mailer data
     */
    protected function createContactFromMailerData(
        array $data,
        Lead $lead
    ): Contact {
        $contactSlug = $this->generateContactSlug($data);
        $contactExistance = $this->checkContactSlugExistance($contactSlug);

        if (! empty($contactExistance['existanceCount'])) {
            throw new \InvalidArgumentException(
                implode('</br>', $contactExistance['message'])
            );
        }

        $contact = Contact::create([
            'c_first_name' => $data['contact_firstname'],
            'c_last_name' => $data['contact_lastname'],
            'c_full_name' => $data['contact_firstname'].' '.$data['contact_lastname'],
            'c_address1' => $data['contact_address'],
            'c_title' => $data['contact_title'],
            'c_phone' => $data['phone'],
            'c_email' => $data['email_address'],
            'c_agent_id' => $data['user_id'],
            'c_status' => $data['contact_status'],
            'contact_slug' => $contactSlug,
        ]);

        // Associate contact with lead
        $contact->leads()->associate($lead);
        $contact->save();

        create_log($lead, 'Create Contact: '.$contact->c_full_name, '');

        return $contact;
    }

    /**
     * Generate unique contact slug
     */
    protected function generateContactSlug(array $data): string
    {
        $addressWithNumber = preg_match(
            '/\d+/',
            $data['contact_address'],
            $matches
        ) ? $matches[0] : $data['contact_address'];

        return $this->generateSlug([
            $data['contact_firstname'],
            $data['contact_lastname'],
            $addressWithNumber,
        ]);
    }

    /**
     * Update lead status from contact
     */
    protected function updateLeadStatusFromContact(
        Lead $lead,
        Contact $contact,
        array $data
    ): void {
        // Update lead pipeline status from contact status
        $this->updateLeadPipelineStatus($lead, $contact);

        // Update dialing list for contact based on status
        $this->updateDialingListForContact($lead, $contact);

        // Create agent status log for the status change
        $this->createAgentStatusLog($lead, $contact);
    }

    /**
     * Update lead's pipeline status from contact
     */
    protected function updateLeadPipelineStatus(Lead $lead, Contact $contact): void
    {
        // Get old status before update
        $oldStatusId = $lead->pipeline_status_id;

        // Update lead's pipeline_status_id from contact's c_status
        $lead->pipeline_status_id = $contact->c_status;

        // Update lead's pipeline_agent_id from contact's c_agent_id
        $lead->pipeline_agent_id = $contact->c_agent_id;

        $lead->save();
    }

    /**
     * Handle dialing list updates based on contact status
     */
    protected function updateDialingListForContact(Lead $lead, Contact $contact): void
    {
        // Get the Dialing model
        $dialing = \App\Model\Dialing::where('lead_number', $lead->id)->first();

        if ($dialing) {
            // Update the status based on contact status
            $dialing->status = $contact->c_status;
            $dialing->save();
        }
    }

    /**
     * Create agent log for status change
     */
    protected function createAgentStatusLog(Lead $lead, Contact $contact): void
    {
        // Get status name
        $statusName = \App\Model\ContactStatus::where('id', $contact->c_status)->value('name') ?? 'Unknown';

        // Create agent log entry
        AgentLog::create([
            'user_id' => $contact->c_agent_id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'message' => 'Status changed to: ' . $statusName,
            'status' => $contact->c_status,
        ]);
    }

    /**
     * Create status note if provided
     */
    protected function createStatusNoteIfProvided(
        array $data,
        Lead $lead,
        Contact $contact
    ): void {
        if (! empty($data['status_note'])) {
            $note = new Note([
                'title' => 'Initial Mailer Form Mail Note',
                'description' => $data['status_note'],
            ]);
            $note->leads()->associate($lead);
            $note->contacts()->associate($contact);
            $note->save();

            create_log($lead, 'Create Note: '.$note->title, '');
        }
    }

    /**
     * Validate mail lead tracker request
     */
    protected function validateMailLeadTrackerRequest(Request $request): array
    {
        $rules = [
            'mailer_id' => 'nullable|exists:mailer_leads_tracker,id',
            'business_address' => 'nullable|string|max:191|regex:/^\d.*/',
            'contact_address' => 'nullable|string|max:191|regex:/^\d.*/',
        ];

        $niceNames = [
            'mailer_id' => 'Mailer ID',
            'business_address' => 'Business Address',
            'contact_address' => 'Contact Address',
        ];

        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => $validator->errors()->first(),
            ];
        }

        return ['valid' => true];
    }

    /**
     * Save or update mail lead tracker
     */
    protected function saveMailLeadTrackerRecord(array $data): array
    {
        $msg = [
            'error' => false,
            'msg' => '',
        ];

        $data['status'] = 0;

        if (empty($data['mailer_id'])) {
            $report = MailerLeadTracker::create($data);
            $msg['msg'] = 'Mailer Lead Tracker saved successfully.';
        } else {
            $report = MailerLeadTracker::findOrFail($data['mailer_id']);
            $report->update($data);
            $msg['msg'] = 'Mailer Lead Tracker updated successfully.';
        }

        return [
            'error' => $msg['error'],
            'msg' => $msg['msg'],
            'report' => $report,
        ];
    }

    /**
     * Save AOR data for activity report
     */
    protected function saveAorData($report, ?array $aorData): void
    {
        if (! $aorData || ! is_array($aorData)) {
            return;
        }

        $ins = [];
        foreach ($aorData as $aor) {
            $ins[] = [
                'activity_report_id' => $report->id,
                'aor' => $aor['aor'] ?? null,
                'aor_community_name' => $aor['aor_community_name'] ?? null,
                'aor_effective_date' => $aor['aor_effective_date'] ?? null,
                'expiring_aor_premium' => $aor['expiring_aor_premium'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($ins) > 0) {
            ActivityReportAor::insert($ins);
        }
    }

    /**
     * Save signed AOR documents
     */
    protected function saveSignedAorDocuments($report, $files): void
    {
        if (! $files || ! is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            $originalName = pathinfo(
                $file->getClientOriginalName(),
                PATHINFO_FILENAME
            );
            $extension = $file->getClientOriginalExtension();
            $timestamp = time();

            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
            $fileName = $safeName.$timestamp.'.'.$extension;

            $relativePath = $file->storeAs(
                'uploads/signed_docs',
                $fileName,
                'public'
            );
            $publicUrl = 'storage/'.$relativePath;

            ActivityReportFile::create([
                'activity_report_id' => $report->id,
                'file_path' => $publicUrl,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
            ]);
        }
    }

    /**
     * Check if duplicate activity report exists
     */
    protected function isDuplicateActivityReport(
        string $date,
        int $userId
    ): bool {
        return ActivityReport::where('date', $date)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get formatted redirection based on user permissions
     */
    protected function getRedirectionBasedOnPermission($user, string $permission): bool
    {
        return $user->can($permission);
    }

    /**
     * Activity list query
     */
    protected function activityListQuery($requestData, $aor = false, $managerId = 0)
    {
        $formattedDate = $this->getFormatedDate($requestData);

        if ($requestData['view_type'] == 1) {
            $query = ActivityReport::query();
            if ($aor) {
                $query = $query->with(['agent', 'aor', 'leads']);
            } else {
                $query = $query->with(['agent', 'leads']);
            }
        } else {
            $query = ActivityReport::join(
                'users',
                'users.id',
                '=',
                'activity_reports.user_id'
            )
                ->leftjoin(
                    'leads',
                    'activity_reports.community_id',
                    '=',
                    'leads.id'
                )
                ->groupBy('user_id')
                ->select(
                    'user_id',
                    'users.name as agent_name',
                    'leads.name as community_name',
                    DB::raw('COUNT(*) as total_lead'),
                    DB::raw('SUM(appointments) as total_appointments'),
                    DB::raw('SUM(policies) as total_policies'),
                    DB::raw(
                        'SUM(expiry_policies_premium) as total_expiry_policies_premium'
                    ),
                );
        }

        if (! empty($formattedDate['from']) && ! empty($formattedDate['to'])) {
            $query->whereBetween('date', [
                $formattedDate['from'],
                $formattedDate['to'],
            ]);
        }

        if (! empty($requestData['agent'])) {
            $query->where('user_id', $requestData['agent']);
        } elseif ($managerId > 0) {
            $accountIds = $this->getAllAccountIdsForManager($managerId);

            if (! empty($accountIds)) {
                $query->whereIn('user_id', $accountIds);
            }
        }

        return $query;
    }

    /**
     * Generate mail lead tracker data
     */
    protected function generateMailLeadTrackerData(
        $requestData,
        $managerId = 0
    ) {
        $formattedDate = $this->getFormatedDate($requestData);

        if ($requestData['view_type'] == 1) {
            $query = MailerLeadTracker::query()
                ->with(['leadSource', 'agent']);
        } else {
            $query = MailerLeadTracker::join(
                'users',
                'users.id',
                '=',
                'mailer_leads_tracker.user_id'
            )
                ->groupBy('user_id')
                ->select(
                    'user_id',
                    'users.name as agent_name',
                    DB::raw('COUNT(*) as total_lead')
                );
        }

        if (! empty($requestData['lead_source'])) {
            $query->where('lead_source', $requestData['lead_source']);
        }

        if (! empty($requestData['agent'])) {
            $query->where('user_id', $requestData['agent']);
        } else {
            $accountIds = $this->getAllAccountIdsForManager($managerId);

            if (! empty($accountIds)) {
                $query->whereIn('user_id', $accountIds);
            }
        }

        if (! empty($formattedDate['from']) && ! empty($formattedDate['to'])) {
            $query->whereBetween('mailer_leads_tracker.date', [
                date('Y-m-d', strtotime($formattedDate['from'])),
                date('Y-m-d', strtotime($formattedDate['to'])),
            ]);
        }

        return $query;
    }

    /**
     * Generate daily report data
     */
    protected function generateDailyReportData(
        $agent = null,
        $from = null,
        $to = null,
        $managerId = 0
    ) {
        $usersQuery = User::select('id', 'name', 'bigoceanuser_id')
            ->whereNotNull('bigoceanuser_id');

        if (! empty($agent)) {
            $usersQuery->where('id', $agent);
        } else {
            $accountIds = $this->getAllAccountIdsForManager($managerId);

            if (! empty($accountIds)) {
                $usersQuery->whereIn('id', $accountIds);
            }
        }

        $users = $usersQuery->get();

        return $users->map(function ($user) use ($from, $to) {
            return $this->buildDailyReportRow($user, $from, $to);
        });
    }

    /**
     * Build daily report row for a user
     */
    protected function buildDailyReportRow($user, $from, $to)
    {
        $dcrQuery = DailyCallReportLog::where(
            'user_franklin_id',
            $user->bigoceanuser_id
        );
        $mailerQuery = MailerLeadTracker::where('user_id', $user->id);
        $activityQuery = ActivityReport::where('user_id', $user->id);

        if ($from && $to) {
            $dcrQuery->whereBetween('call_begin', [$from, $to]);
            $mailerQuery->whereBetween('created_at', [$from, $to]);
            $activityQuery->whereBetween('date', [
                date('Y-m-d', strtotime($from)),
                date('Y-m-d', strtotime($to)),
            ]);
        }

        $outboundCalls = (clone $dcrQuery)
            ->where('call_type', 'Outbound')
            ->count();

        $leadCounts = $this->getLeadSourceCounts($mailerQuery);

        $appointments = (clone $activityQuery)
            ->sum(DB::raw('IFNULL(appointments, 0)'));
        $policies = (clone $activityQuery)
            ->sum(DB::raw('IFNULL(policies, 0)'));
        $expiryPremium = (clone $activityQuery)
            ->sum(DB::raw('IFNULL(expiry_policies_premium, 0)'));

        $activityIds = (clone $activityQuery)->pluck('id');
        $aorDetails = ActivityReportAor::whereIn(
            'activity_report_id',
            $activityIds
        )->get();

        $aorSum = $aorDetails->sum(function ($row) {
            return is_numeric($row->aor) ? $row->aor : 0;
        });

        $aorMonthCsv = $aorDetails->pluck('aor_effective_date')
            ->filter()
            ->map(function ($date) {
                try {
                    return Carbon::parse($date)->format('F');
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->unique()
            ->implode(', ');

        $aorPremiumSum = $aorDetails->sum(function ($row) {
            return is_numeric($row->expiring_aor_premium)
                ? $row->expiring_aor_premium
                : 0;
        });

        return [
            'producer_name' => $user->name,
            'outbound_calls' => $outboundCalls,
            'facebook' => $leadCounts['Facebook'] ?? 0,
            'mailer' => $leadCounts['Mailer'] ?? 0,
            'sms' => $leadCounts['SMS'] ?? 0,
            'email' => $leadCounts['Email'] ?? 0,
            'transfer_611' => $leadCounts['611 Transfer'] ?? 0,
            'referal_611' => $leadCounts['611 Referral Email'] ?? 0,
            'appointments' => $appointments,
            'policies' => $policies,
            'expiry_premium' => $expiryPremium,
            'aor' => $aorSum,
            'aor_effective_month' => $aorMonthCsv,
            'aor_premium' => $aorPremiumSum,
        ];
    }

    /**
     * Get lead source counts
     */
    protected function getLeadSourceCounts($mailerQuery): array
    {
        $leadSourcesArr = [
            'Facebook',
            'Mailer',
            'SMS',
            'Email',
            '611 Transfer',
            '611 Referral Email',
        ];

        $leadCounts = [];

        foreach ($leadSourcesArr as $source) {
            $sourceIds = LeadSource::where('status', 1)
                ->where('name', 'like', '%'.$source.'%')
                ->pluck('id')
                ->toArray();

            $leadCounts[$source] = (clone $mailerQuery)
                ->whereIn('lead_source', $sourceIds)
                ->count();
        }

        return $leadCounts;
    }

    /**
     * Get formatted date from request data
     */
    protected function getFormatedDate($requestData)
    {
        $from = $to = null;

        if (isset($requestData['date_range']) && $requestData['date_range']) {
            $now = Carbon::now();

            switch ($requestData['date_range']) {
                case 'yesterday':
                    $from = $now->copy()->subDay()->startOfDay();
                    $to = $now->copy()->subDay()->endOfDay();
                    break;
                case 'last_7_days':
                    $from = $now->copy()->subDays(6)->startOfDay();
                    $to = $now->endOfDay();
                    break;
                case 'last_30_days':
                    $from = $now->copy()->subDays(29)->startOfDay();
                    $to = $now->endOfDay();
                    break;
                case 'custom':
                    if (isset($requestData['from_date']) &&
                        $requestData['from_date'] &&
                        isset($requestData['to_date']) &&
                        $requestData['to_date']) {
                        $from = Carbon::parse($requestData['from_date'])
                            ->startOfDay();
                        $to = Carbon::parse($requestData['to_date'])->endOfDay();
                    }
                    break;
                case 'custom_days':
                    if (isset($requestData['custom_days']) &&
                        is_numeric($requestData['custom_days'])) {
                        $from = $now->copy()
                            ->subDays($requestData['custom_days'] - 1)
                            ->startOfDay();
                        $to = $now->endOfDay();
                    }
                    break;
                default:
                    $from = $now->copy()->subDay()->startOfDay();
                    $to = $now->endOfDay();
                    break;
            }
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * Safely format date
     */
    protected function safeDate($date)
    {
        try {
            if (! $date || $date == '0000-00-00') {
                return '';
            }

            return Carbon::parse($date)->format('m/d/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Safely format number
     */
    protected function safeNumberFormat($num, $decimals = 2)
    {
        $clean = preg_replace('/[^0-9.\-]/', '', $num);

        return number_format(
            (float) (is_numeric($clean) ? $clean : 0),
            $decimals
        );
    }

    /**
     * Format AOR details for export
     */
    protected function formatAorDetails($activityReports)
    {
        $formatted = [];

        foreach ($activityReports as $report) {
            $item = [
                'id' => $report->id,
                'date' => $this->safeDate($report->date),
                'agent_name' => optional($report->agent)->name,
                'appointments' => $report->appointments,
                'policies' => $report->policies,
                'expiry_policies_premium' => $this->safeNumberFormat(
                    $report->expiry_policies_premium,
                    2
                ),
                'community_name' => $report->community_name,
                'aor_breakdown' => $report->aor_breakdown,
            ];

            $aors = $report->aor->take(5);

            foreach ($aors as $index => $aor) {
                $i = $index + 1;

                $item["aor{$i}"] = $aor->aor ?? '';
                $item["aor_community_name{$i}"] = $aor->aor_community_name ?? '';
                $item["aor_effective_date{$i}"] = $this->safeDate(
                    $aor->aor_effective_date
                );
                $item["expiring_aor_premium{$i}"] = $this->safeNumberFormat(
                    $aor->expiring_aor_premium,
                    2
                );
            }

            // Fill missing AORs with N/A if less than 5
            for ($i = count($aors) + 1; $i <= 5; $i++) {
                $item["aor{$i}"] = 'N/A';
                $item["aor_community_name{$i}"] = '';
                $item["aor_effective_date{$i}"] = '';
                $item["expiring_aor_premium{$i}"] = '0';
            }

            $formatted[] = $item;
        }

        return $formatted;
    }
}
