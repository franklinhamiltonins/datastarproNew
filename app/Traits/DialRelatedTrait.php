<?php

namespace App\Traits;

use App\Http\Controllers\Controller;
use App\Model\LeadsModel\Lead;
use Illuminate\Support\Facades\DB;

trait DialRelatedTrait
{
    /**
     * Get lead column types
     */
    protected function getLeadColumnTypes(): array
    {
        return Lead::Get_column_type();
    }

    /**
     * Build base lead query
     */
    protected function buildBaseLeadQuery($locationLeadIdSearch, $locationLeadId): Lead
    {
        if ($locationLeadIdSearch) {
            return Lead::with('contacts')->whereIn('id', $locationLeadId);
        }

        return Lead::with('contacts');
    }

    /**
     * Get owned lead IDs
     */
    protected function getOwnedLeadIds(): array
    {
        return DB::table('dialings_leads')
            ->where('status', 'owned')
            ->where('owned_by_agent_id', '>', 0)
            ->get()
            ->pluck('lead_id')
            ->toArray();
    }

    /**
     * Get dialing contact statuses
     */
    protected function getDialingContactStatuses(): array
    {
        return Controller::getDialingStatusOptions();
    }

    /**
     * Apply lead filters
     */
    protected function applyLeadFilters($leadsQuery, $searchFields, $columnsType, $campaignId)
    {
        $leadsQuery = filter_leads($leadsQuery, $searchFields, $columnsType, $campaignId);
        $leadsQuery->where('is_client', 0);

        return $leadsQuery;
    }

    /**
     * Exclude owned leads
     */
    protected function excludeOwnedLeads($leadsQuery): void
    {
        $ownedLeads = $this->getOwnedLeadIds();
        if (! empty($ownedLeads)) {
            $leadsQuery->whereNotIn('id', $ownedLeads);
        }
    }

    /**
     * Apply contact status filter
     */
    protected function applyContactStatusFilter($leadsQuery): void
    {
        $dialingContactStatus = $this->getDialingContactStatuses();

        $leadsQuery->whereHas('contacts', function ($query) use ($dialingContactStatus) {
            $query->where('c_phone', '<>', '');
            $query->whereIn('c_status', $dialingContactStatus);
        });
    }

    public function leadOutputGetFordialing($locationLeadIdSearch, $locationLeadId, $searchFields, $campaignId)
    {
        $columnsType = $this->getLeadColumnTypes();
        $leadsQuery = $this->buildBaseLeadQuery($locationLeadIdSearch, $locationLeadId);
        $leadsQuery = $this->applyLeadFilters($leadsQuery, $searchFields, $columnsType, $campaignId);
        $this->excludeOwnedLeads($leadsQuery);
        $this->applyContactStatusFilter($leadsQuery);

        return $leadsQuery;
    }
}
