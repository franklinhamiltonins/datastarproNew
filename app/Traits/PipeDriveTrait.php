<?php

namespace App\Traits;

use App\Model\AsanaQuestion;
use App\Model\AsanaQuestionDetail;
use App\Model\ContactStatus;
use App\Model\InsuranceType;
use App\Model\LeadAsanaDetail;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\User;
use Illuminate\Support\Facades\DB;

trait PipeDriveTrait
{
    /**
     * Get lead with all relationships
     */
    protected function getLeadWithRelations($leadId)
    {
        return Lead::with([
            'contacts' => function ($query) {
                $query->orderBy('c_status', 'desc')
                    ->with('contactStatus:id,name');
            },
            'propertyCarrier:id,name',
            'glCarrier:id,name',
            'ciCarrier:id,name',
            'doCarrier:id,name',
            'umbrellaCarrier:id,name',
            'wcCarrier:id,name',
            'floodCarrier:id,name',
            'dcCarrier:id,name',
            'xwindCarrier:id,name',
            'ebCarrier:id,name',
            'caCarrier:id,name',
            'marinaCarrier:id,name',
            'propertyRating:id,name',
            'generaLiablityRating:id,name',
            'crimeInsuranceRating:id,name',
            'directorOfficerRating:id,name',
            'uRating:id,name',
            'workerCompansestionRating:id,name',
            'fRating:id,name',
            'leadSource:id,name',
            'leadAdditionalpolicy.listCarrier:id,name',
        ])
            ->where('id', $leadId)
            ->first();
    }

    /**
     * Build pipeline agent and status data
     */
    protected function buildPipelineData($leadQuery): void
    {
        // Pipeline agent name
        $pipelineAgentName = optional(
            User::select('name')->find($leadQuery->pipeline_agent_id)
        )->name ?? '';

        // Pipeline status name and special marker
        $status = ContactStatus::select('special_marker', 'name')
            ->find($leadQuery->pipeline_status_id);

        // Attach additional fields
        $leadQuery->pipeline_status_name = $status->name ?? '';
        $leadQuery->special_marker = $status->special_marker ?? '';
        $leadQuery->pipeline_agent_name = $pipelineAgentName;
        $leadQuery->lead_source_name = $leadQuery->leadSource->name ?? '';
        $leadQuery->lead_source_id = $leadQuery->leadSource->id ?? '';
    }

    /**
     * Build carrier names for lead
     */
    protected function buildCarrierNames($leadQuery): void
    {
        // Carrier names
        $leadQuery->property_carrier_name = $leadQuery->propertyCarrier->name ?? '';
        $leadQuery->gl_carrier_name = $leadQuery->glCarrier->name ?? '';
        $leadQuery->ci_carrier_name = $leadQuery->ciCarrier->name ?? '';
        $leadQuery->do_carrier_name = $leadQuery->doCarrier->name ?? '';
        $leadQuery->u_carrier_name = $leadQuery->umbrellaCarrier->name ?? '';
        $leadQuery->wc_carrier_name = $leadQuery->wcCarrier->name ?? '';
        $leadQuery->f_carrier_name = $leadQuery->floodCarrier->name ?? '';
        $leadQuery->dic_carrier_name = $leadQuery->dcCarrier->name ?? '';
        $leadQuery->xwind_carrier_name = $leadQuery->xwindCarrier->name ?? '';
        $leadQuery->eb_carrier_name = $leadQuery->ebCarrier->name ?? '';
        $leadQuery->ca_carrier_name = $leadQuery->caCarrier->name ?? '';
        $leadQuery->m_carrier_name = $leadQuery->marinaCarrier->name ?? '';
    }

    /**
     * Build rating names for lead
     */
    protected function buildRatingNames($leadQuery): void
    {
        // Rating names
        $leadQuery->property_rating_name = $leadQuery->propertyRating->name ?? '';
        $leadQuery->gl_rating_name = $leadQuery->generaLiablityRating->name ?? '';
        $leadQuery->ci_rating_name = $leadQuery->crimeInsuranceRating->name ?? '';
        $leadQuery->do_rating_name = $leadQuery->directorOfficerRating->name ?? '';
        $leadQuery->u_rating_name = $leadQuery->uRating->name ?? '';
        $leadQuery->wc_rating_name = $leadQuery->workerCompansestionRating->name ?? '';
        $leadQuery->f_rating_name = $leadQuery->fRating->name ?? '';
    }

    /**
     * Build status list SQL query
     */
    protected function buildStatusListQuery(): string
    {
        return '
            SELECT
                cs.id,
                cs.name,
                u.name AS agent_name,
                SUM(
                    COALESCE(
                        CASE
                            WHEN TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp)) < 1
                            THEN 1
                            ELSE TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp))
                        END, 0)
                ) AS minutes
            FROM contact_status cs
            LEFT JOIN lead_status_wise_log lswl
                ON cs.id = lswl.status_id
                AND lswl.lead_id = ?
            LEFT JOIN users u
                ON u.id = (
                    SELECT agent_id
                    FROM lead_status_wise_log lswl2
                    WHERE lswl2.status_id = cs.id
                      AND lswl2.lead_id = ?
                    ORDER BY lswl2.start_timestamp DESC
                    LIMIT 1
                )
            WHERE cs.display_in_pipedrive IS NOT NULL
              AND cs.deleted_at IS NULL
            GROUP BY cs.id, cs.name, u.name
            ORDER BY cs.priority ASC
        ';
    }

    /**
     * Process status list data
     */
    protected function processStatusList($statusList): array
    {
        foreach ($statusList as $key => $valueStatus) {
            $statusList[$key]->days = ceil($valueStatus->minutes / 1440);
        }

        return $statusList;
    }

    /**
     * Build additional policy carrier names
     */
    protected function buildAdditionalPolicyCarrierNames($leadQuery): void
    {
        $additonalPolicy = $leadQuery->leadAdditionalpolicy;
        foreach ($additonalPolicy as $key => $policy) {
            $policy->carrier_name = $policy->listCarrier->name ?? '';
        }
    }

    public function leadIdBasedData($leadId)
    {
        $leadQuery = $this->getLeadWithRelations($leadId);

        if (! $leadQuery) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $this->buildPipelineData($leadQuery);
        $this->buildCarrierNames($leadQuery);
        $this->buildRatingNames($leadQuery);

        // Raw SQL for status list
        $sqlQuery = $this->buildStatusListQuery();
        $statusList = DB::select($sqlQuery, [$leadId, $leadId]);
        $statusList = $this->processStatusList($statusList);

        // Add carrier names to additional policy
        $this->buildAdditionalPolicyCarrierNames($leadQuery);

        $additonalPolicy = $leadQuery->leadAdditionalpolicy;

        return [
            'lead' => $leadQuery,
            'status_list' => $statusList,
            'additonalPolicy' => $additonalPolicy,
        ];
    }

    /**
     * Get Asana questions for lead
     */
    protected function getAsanaQuestions($leadAsana,$leadId, $asanaStage)
    {
        $questions = AsanaQuestion::select('id', 'name', 'priority')
            ->where('status', 1)
            ->orderBy('priority', 'ASC')
            ->get();

        foreach ($questions as $keyquestion) {
            $keyquestion->leadId = $leadId;
            $stagewiseQuestion = AsanaQuestionDetail::where('asana_question_id', $keyquestion->id)
                ->select('question', 'id', 'short_hand', 'show_selection', 'selection_category', 'category_type', 'selection_short_hand')
                ->get();

            foreach ($stagewiseQuestion as $keystage) {
                $keystage->answer = ! empty($leadAsana->{$keystage->short_hand}) ? $leadAsana->{$keystage->short_hand} : '';
                $keystage->selection_list = ! empty($leadAsana->{$keystage->selection_short_hand}) ? $leadAsana->{$keystage->selection_short_hand} : '';
            }
            $keyquestion->stagewise_question = $stagewiseQuestion;
        }

        return $questions;
    }

    public function getQuestionAsana($leadId)
    {
        $leadAsana = LeadAsanaDetail::where('lead_id', $leadId)->first();

        $asanaStage = ! empty($leadAsana->asana_stage) ? $leadAsana->asana_stage : 1;
        $stageCompleted = ! empty($leadAsana->stage_completed) ? $leadAsana->stage_completed : 0;
        $asanaPriority = 0;

        $questions = $this->getAsanaQuestions($leadAsana,$leadId, $asanaStage);

        foreach ($questions as $keyquestion) {
            if ($keyquestion->id == $asanaStage) {
                $asanaPriority = $keyquestion->priority;
            }
        }

        return [
            'questions' => $questions,
            'asana_stage' => $asanaStage,
            'stage_completed' => $stageCompleted,
            'asana_priority' => $asanaPriority,
        ];
    }

    /**
     * Get carrier list for display
     */
    protected function getCarrierList(): array
    {
        return ['Property', 'General Liability', 'Directors & Officers', 'Legal Defense', 'Umbrella', 'Crime Insurance', 'Workers Compensation', 'Flood'];
    }

    /**
     * Build carrier list with insurance types
     */
    protected function buildCarrierList(): array
    {
        $carrierList = $this->getCarrierList();
        $list = [];

        foreach ($carrierList as $carrier) {
            $ins['name'] = $carrier;
            $type = InsuranceType::where('name', $carrier)->first();
            if ($type) {
                $ins['carrierlist'] = $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray();
            } else {
                $ins['carrierlist'] = collect();
            }

            $list[] = $ins;
        }

        return $list;
    }

    public function carrierListDisplay()
    {
        return $this->buildCarrierList();
    }

    public function getAsanaStagePriority($asanaStageId)
    {
        $priority = AsanaQuestion::whereKey($asanaStageId)->value('priority');

        if ($priority === null) {
            return null;
        }

        return AsanaQuestion::where('priority', $priority + 1)->value('id');
    }

    public function updateContactleadsData($contactchecking, $additionalInfo, $leadsTable, $type)
    {
        return $type == 1
            ? $this->updateExistingContact($contactchecking, $additionalInfo)
            : $this->createNewContact($additionalInfo, $leadsTable);
    }

    public function updateExistingContact($contactchecking, $additionalInfo)
    {
        $contact = Contact::withTrashed()->find($contactchecking->id);

        if (! $contact) {
            return;
        }

        if ($contact->trashed()) {
            $contact->restore();
        }

        $contact->c_phone = $additionalInfo->phone;

        if (! empty($additionalInfo->email)) {
            $contact->c_email = $additionalInfo->email;
        }

        if (empty($contact->fake_address)) {
            $contact->fake_address = 2;
        }

        $contact->save();
    }

    public function createNewContact($additionalInfo, $leadsTable)
    {
        $contact = new Contact;

        $contact->lead_id = $leadsTable->id;
        $contact->c_first_name = $additionalInfo->firstname;
        $contact->c_last_name = $additionalInfo->lastname;
        $contact->c_full_name = trim($additionalInfo->firstname.' '.$additionalInfo->lastname);
        $contact->c_address1 = $leadsTable->address1;
        $contact->fake_address = 1;
        $contact->c_phone = $additionalInfo->phone;

        // Extract street number if exists
        preg_match('/\d+/', $leadsTable->address1, $matches);
        $addressNumber = $matches[0] ?? $leadsTable->address1;

        $contact->contact_slug = $this->generateSlug([
            $additionalInfo->firstname,
            $additionalInfo->lastname,
            $addressNumber,
        ]);

        if (! empty($additionalInfo->email)) {
            $contact->c_email = $additionalInfo->email;
        }

        $contact->save();
    }

    /**
     * Get permissions to check
     */
    protected function getPermissionsToCheck(): array
    {
        return [
            'isAdminUser' => 'agent-create',
            'is_admin_user' => 'agent-create',
            'accountListPermission' => 'all-accounts-list-pipedrive',
            'pipemng_acess' => 'pipe-management',
            'pipemng_acess_download' => 'pipe-management-download',
            'bindmng_acess' => 'bind-management',
            'bindmng_acess_download' => 'bind-management-download',
            'contact_delete' => 'contact-delete',
            'contact_create' => 'contact-create',
            'contact_edit' => 'contact-edit',
            'lead_file_list' => 'lead-file-list',
            'lead_file_upload' => 'lead-file-upload',
            'lead_file_download' => 'lead-file-download',
            'lead_file_delete' => 'lead-file-delete',
            'lead_edit' => 'lead-edit',
            'lead_create' => 'lead-create',
        ];
    }

    /**
     * Evaluate user permissions
     */
    protected function evaluateUserPermissions($user, array $permissions): array
    {
        $result = [];

        foreach ($permissions as $key => $permission) {
            $result[$key] = $user->can($permission);
        }

        return $result;
    }

    public function getAgentWisePermission($user)
    {
        $permissions = $this->getPermissionsToCheck();
        $result = $this->evaluateUserPermissions($user, $permissions);

        // Special case for isAdminUser fallback
        if (! $result['isAdminUser']) {
            $result['isAdminUser'] = $result['accountListPermission'];
        }

        return $result;
    }

    public function getAgentSelectionValidation($statusName)
    {
        return 'Selecting an agent is mandatory with '.$statusName.' status';
    }
}
