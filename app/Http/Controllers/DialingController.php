<?php

namespace App\Http\Controllers;

use App\Jobs\AgentReassignJob;
use App\Jobs\DialCreateJob;
use App\Model\Agentlistlead;
use App\Model\AgentLog;
use App\Model\Calllog;
use App\Model\Dialing;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\Setting;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use App\Traits\DialRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing dialing operations including lead assignment, ownership, and viewing.
 */
class DialingController extends Controller
{
    use CommonFunctionsTrait, DialRelatedTrait, SMTPRelatedTrait;

    /** @var bool Indicates if the current user is an admin */
    private $isAdmin = false;

    /** @var int Current authenticated user ID */
    private $currentUserId = 0;

    /** @var bool Flag to check if user context is initialized */
    private $userContextInitialized = false;

    /**
     * Initialize user context (admin status and user ID) - lazy initialization
     */
    private function initializeUserContext(): void
    {
        // Prevent multiple initializations
        if ($this->userContextInitialized) {
            return;
        }

        // Check if user is authenticated before accessing user properties
        if (auth()->check()) {
            $this->isAdmin = auth()->user()->can('agent-create');
            $this->currentUserId = auth()->user()->id;
        }

        $this->userContextInitialized = true;
    }

    /**
     * Get admin status for current user
     * @return bool True if user has admin privileges
     */
    private function getAdminStatus(): bool
    {
        $this->initializeUserContext();
        return $this->isAdmin;
    }

    /**
     * Get current authenticated user ID
     * @return int Current user ID
     */
    private function getCurrentUserId(): int
    {
        $this->initializeUserContext();
        return $this->currentUserId;
    }

    /**
     * Update leads that have duplicate ownership - keeps only the most recent ownership
     * Finds leads with multiple 'own' status entries and resets all but the latest
     */
    public function updateDialingLead(): void
    {
        // Query leads that have multiple ownership records
        $duplicateLeadData = $this->getDuplicateOwnershipLeads();

        if (count($duplicateLeadData) > 0) {
            // Reset all duplicate ownerships to 'free' status
            $this->resetDuplicateOwnerships($duplicateLeadData);
        }
    }

    /**
     * Get leads that have multiple ownership records
     * @return array List of leads with duplicate ownership
     */
    private function getDuplicateOwnershipLeads(): array
    {
        return DB::select("
            SELECT leads.id, COUNT(*) as total
            FROM `leads`
            INNER JOIN `dialings_leads` ON `leads`.`id` = `dialings_leads`.`lead_id`
            INNER JOIN `users` ON `dialings_leads`.`owned_by_agent_id` = `users`.`id`
            WHERE `dialings_leads`.`owned_by_agent_id` > 0
            AND `dialings_leads`.`status` = 'own'
            AND `leads`.`deleted_at` IS NULL
            GROUP BY `leads`.id
            HAVING total > 1
        ");
    }

    /**
     * Reset duplicate ownerships to free status
     * @param array $duplicateLeadData Leads with duplicate ownership
     */
    private function resetDuplicateOwnerships(array $duplicateLeadData): void
    {
        $updateData = ['owned_by_agent_id' => 0, 'status' => 'free', 'ownmarked_at' => null];

        foreach ($duplicateLeadData as $lead) {
            $latestDialingId = $this->getLatestOwnedDialingId($lead->id);

            if (!empty($latestDialingId)) {
                DB::table('dialings_leads')
                    ->where('lead_id', $lead->id)
                    ->where('status', 'own')
                    ->where('dialing_id', '!=', $latestDialingId)
                    ->update($updateData);
            }
        }
    }

    /**
     * Get the most recent dialing ID for a lead with 'own' status
     * @param int $leadId Lead ID to query
     * @return int|null Latest dialing ID or null
     */
    private function getLatestOwnedDialingId(int $leadId): ?int
    {
        return DB::table('dialings_leads')
            ->where('lead_id', $leadId)
            ->where('status', 'own')
            ->orderByDesc('dialing_id')
            ->value('dialing_id');
    }

    /**
     * Display the dialing index page with agent list
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $isAdminUser = $this->getAdminStatus();
        $agentUsers = $this->buildAgentListForDisplay();

        $vars = ['agentUsers', 'isAdminUser'];

        // Trigger websocket event for lead click notification
        $clickDetails = json_encode([
            'agentId' => 1,
            'contactId' => 2,
            'leadId' => 3,
            'dialingId' => 4,
        ]);
        event(new \App\Events\LeadclickedWebsocket($clickDetails));

        return view('dialings.index', compact($vars));
    }

    /**
     * Build agent list for dropdown display
     * @return array Agent list with ID as key and 'name(email)' as value
     */
    private function buildAgentListForDisplay(): array
    {
        $agents = User::role('Agent')->get();
        $agentUsers = [];

        foreach ($agents as $agent) {
            $agentUsers[$agent->id] = $agent->name . '(' . $agent->email . ')';
        }

        return $agentUsers;
    }

    /**
     * API endpoint for dialing list data with datatables format
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dialingListApi(Request $request)
    {
        $isAdmin = $this->getAdminStatus();
        $dialingQuery = $this->buildDialingListQuery($isAdmin);

        $dialings = $this->getDialingList($dialingQuery, $isAdmin);

        return datatables()->of($dialings)
            ->addIndexColumn()
            ->addColumn('status', function ($row) use ($isAdmin) {
                return $this->buildStatusColumn($row, $isAdmin);
            })
            ->addColumn('action', function ($row) use ($isAdmin) {
                return $this->buildActionColumn($row, $isAdmin);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Build base query for dialing list
     * @param bool $isAdmin Whether user is admin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildDialingListQuery(bool $isAdmin)
    {
        return Dialing::select('dialings.id', 'dialings.referral_marker', 'dialings.name', 'dialings.status');
    }

    /**
     * Get dialing list based on admin status
     * @param \Illuminate\Database\Eloquent\Builder $dialingQuery Base query
     * @param bool $isAdmin Whether user is admin
     * @return \Illuminate\Collection
     */
    private function getDialingList($dialingQuery, bool $isAdmin)
    {
        if ($isAdmin) {
            $dialings = $dialingQuery->get();
        } else {
            $dialings = $dialingQuery->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
                ->whereNull('dialings.deleted_at')
                ->where('status', 'Active')
                ->where('dialing_user.user_id', $this->getCurrentUserId())->get();
        }

        foreach ($dialings as $dialing) {
            $this->attachAgentInfo($dialing, $isAdmin);
        }

        return $dialings;
    }

    /**
     * Attach agent information to dialing record
     * @param object $dialing Dialing record
     * @param bool $isAdmin Whether user is admin
     */
    private function attachAgentInfo(object $dialing, bool $isAdmin): void
    {
        if (!$isAdmin) {
            $currentUser = auth()->user();
            $dialing->agent_name = $currentUser->name;
            $dialing->agent_ids = $currentUser->id;
        } else {
            $dialing->agent_name = $dialing->users->pluck('name')->implode(', ');
            $dialing->agent_ids = $dialing->users->pluck('id')->implode(', ');
        }
    }

    /**
     * Build status column HTML for datatables
     * @param object $row Data row
     * @param bool $isAdmin Whether user is admin
     * @return \Illuminate\View\View
     */
    private function buildStatusColumn(object $row, bool $isAdmin)
    {
        $deleteLead = 'lead-delete';
        $crudRoutePart = 'lead';
        $statusOptions = ['Active', 'Inactive'];
        $selectedStatus = $row->status;

        return view('dialings.partials.status-select', compact('deleteLead', 'crudRoutePart', 'row', 'isAdmin', 'statusOptions', 'selectedStatus'));
    }

    /**
     * Build action column HTML for datatables
     * @param object $row Data row
     * @param bool $isAdmin Whether user is admin
     * @return \Illuminate\View\View
     */
    private function buildActionColumn(object $row, bool $isAdmin)
    {
        $deleteLead = 'lead-delete';
        $crudRoutePart = 'lead';

        return view('dialings.partials.buttons-actions', compact('deleteLead', 'crudRoutePart', 'row', 'isAdmin'));
    }

    /**
     * Get owned leads for the current user (for datatables)
     * @param Request $request HTTP request with pagination and filter params
     * @return \Illuminate\Http\JsonResponse
     */
    public function dialingsOwnedLeads(Request $request)
    {
        $params = $this->extractListRequestParams($request);

        $isAdmin = $this->getAdminStatus();
        $currentUserId = $this->getCurrentUserId();
        $agentListId = $this->extractAgentListId($request);

        $ownedQuery = $this->buildOwnedLeadsBaseQuery($currentUserId);

        if ($isAdmin) {
            $this->applyAdminOwnedLeadsFilter($ownedQuery);
        } else {
            $this->applyUserOwnedLeadsFilter($ownedQuery, $currentUserId);
        }

        $totalRecords = $ownedQuery->count();
        $filteredRecords = $totalRecords;

        if (!empty($params['searchValue'])) {
            $ownedQuery = $this->applySearchFilter($ownedQuery, $params['searchValue']);
            $filteredRecords = $ownedQuery->count();
        }

        $ownedQuery->orderBy($params['filterOnColumnName'], $params['orderBy'])
            ->offset($params['start'])
            ->limit($params['length']);

        $pageType = 'dialingShow';
        $agentId = $this->getCurrentUserId();
        $ownedContactStatus = self::getOwnedContactStatusOptions();

        return datatables()->of($ownedQuery)
            ->addIndexColumn()
            ->addColumn('business_contacts', function ($row) use ($pageType, $agentListId, $agentId, $ownedContactStatus) {
                return $this->buildContactsColumn($row, $pageType, $agentListId, $agentId, $ownedContactStatus);
            })
            ->rawColumns(['business_contacts'])
            ->addColumn('action', function ($row) {
                return $this->buildAgentLeadsActionColumn($row);
            })
            ->rawColumns(['action'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filteredRecords)
            ->make(true);
    }

    /**
     * Extract common list request parameters
     * @param Request $request HTTP request
     * @return array Extracted parameters
     */
    private function extractListRequestParams(Request $request): array
    {
        return [
            'start' => $request->input('start', 0),
            'length' => $request->input('length', 10),
            'draw' => $request->input('draw', 1),
            'filterOnColumnNumber' => $request->input('order')[0]['column'] ?? 0,
            'filterOnColumnName' => $request->input('columns')[$request->input('order')[0]['column'] ?? 0]['data'] ?? 'id',
            'orderBy' => $request->input('order')[0]['dir'] ?? 'desc',
            'searchValue' => $request->input('search')['value'] ?? null,
        ];
    }

    /**
     * Extract agent list ID from request
     * @param Request $request HTTP request
     * @return int Agent list ID
     */
    private function extractAgentListId(Request $request): int
    {
        return !empty($request->agentlist_id) ? $request->agentlist_id : 0;
    }

    /**
     * Build base query for owned leads
     * @param int $currentUserId Current user ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildOwnedLeadsBaseQuery(int $currentUserId)
    {
        return Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
            ->join('users', 'dialings_leads.owned_by_agent_id', '=', 'users.id')
            ->select('leads.*', 'users.name as owned_by', 'users.id as owned_by_id', 'dialings_leads.dialing_id');
    }

    /**
     * Apply filter for admin users to get all owned leads
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     */
    private function applyAdminOwnedLeadsFilter($query): void
    {
        $query->distinct('leads.id')
            ->where('dialings_leads.owned_by_agent_id', '>', 0)
            ->where('dialings_leads.status', 'own');
    }

    /**
     * Apply filter for non-admin users to get their owned leads
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     * @param int $currentUserId Current user ID
     */
    private function applyUserOwnedLeadsFilter($query, int $currentUserId): void
    {
        $query->distinct('leads.id')
            ->where('dialings_leads.owned_by_agent_id', $currentUserId)
            ->where('dialings_leads.status', 'own');
    }

    /**
     * Apply search filter to query
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     * @param string $searchValue Search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applySearchFilter($query, string $searchValue)
    {
        return $query->where(function ($query) use ($searchValue) {
            $query->Where('leads.name', 'like', '%' . $searchValue . '%')
                ->orWhere('leads.city', 'like', '%' . $searchValue . '%')
                ->orWhere('leads.county', 'like', '%' . $searchValue . '%')
                ->orWhere('leads.unit_count', 'like', '%' . $searchValue . '%');
        });
    }

    /**
     * Build business contacts column for datatables
     * @param object $row Data row
     * @param string $pageType Page type identifier
     * @param int $agentListId Agent list ID
     * @param int $agentId Agent ID
     * @param array $contactStatus Allowed contact statuses
     * @return \Illuminate\View\View
     */
    private function buildContactsColumn(object $row, string $pageType, int $dialingId, int $agentId, array $contactStatus)
    {
        $view = 'view';
        $businessContacts = $row->contacts->filter(function ($contact) use ($contactStatus) {
            return $contact->c_phone != '' && in_array($contact->c_status, $contactStatus);
        });

        return view('dialings.partials.contacts-action', compact('view', 'row', 'businessContacts', 'pageType', 'dialingId', 'agentId'));
    }

    /**
     * Build action column for agent leads
     * @param object $row Data row
     * @return \Illuminate\View\View
     */
    private function buildAgentLeadsActionColumn(object $row)
    {
        $deleteLead = 'lead-delete';
        $crudRoutePart = 'lead';
        $isAdmin = $this->getAdminStatus();

        return view('dialings.partials.buttons-actions-agentleads', compact('deleteLead', 'crudRoutePart', 'row', 'isAdmin'));
    }

    /**
     * Update lead ownership to a different agent
     * @param Request $request HTTP request with reassign params
     * @param int $leadId Lead ID to reassign
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOwner(Request $request, int $leadId)
    {
        $reassignDialingId = $request->reassign_dialing_id;
        $reassignAgentId = $request->reassign_agent_id;

        // Check if the agent is associated with the dialing
        $checkEntry = $this->checkAgentDialingAssociation($reassignDialingId, $reassignAgentId);

        if (!$checkEntry) {
            return $this->handleReassignFailure($request, $reassignDialingId);
        }

        // Update lead ownership in dialing_leads table
        $this->updateLeadOwnership($reassignDialingId, $leadId, $reassignAgentId);

        // Update contact's agent assignment
        Contact::where('c_agent_id', $request->old_agent_id)
            ->where('lead_id', $leadId)
            ->update(['c_agent_id' => $reassignAgentId]);

        // Update lead's pipeline agent
        Lead::where('id', $leadId)
            ->where('pipeline_agent_id', $request->old_agent_id)
            ->update(['pipeline_agent_id' => $reassignAgentId]);

        return response()->json([
            'status' => true,
            'message' => 'Own Lead agent Updated Successfully',
        ], 200);
    }

    /**
     * Check if agent is associated with dialing
     * @param int $dialingId Dialing ID
     * @param int $agentId Agent ID
     * @return object|null Association record or null
     */
    private function checkAgentDialingAssociation(int $dialingId, int $agentId): ?object
    {
        return DB::table('dialing_user')
            ->where('dialing_id', $dialingId)
            ->where('user_id', $agentId)
            ->first();
    }

    /**
     * Handle reassign when agent is not associated with dialing
     * @param Request $request HTTP request
     * @param int $dialingId Dialing ID
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleReassignFailure(Request $request, int $dialingId)
    {
        if (!empty($request->force_update)) {
            DB::table('dialing_user')->insert([
                'dialing_id' => $dialingId,
                'user_id' => $request->reassign_agent_id,
            ]);
            return response()->json(['status' => true], 200);
        }

        $agentNames = $this->getAssociatedAgentNames($dialingId);

        return response()->json([
            'status' => false,
            'agent_name' => $agentNames,
            'message' => 'This Dialing is not associated with this user, it is associated with ' . $agentNames,
        ], 200);
    }

    /**
     * Get names of agents associated with a dialing
     * @param int $dialingId Dialing ID
     * @return string Comma-separated agent names
     */
    private function getAssociatedAgentNames(int $dialingId): string
    {
        $agents = DB::table('dialing_user')
            ->where('dialing_id', $dialingId)
            ->join('users', 'dialing_user.user_id', '=', 'users.id')
            ->select('users.name')
            ->get();

        $agentName = '';
        foreach ($agents as $user) {
            $agentName .= !empty($agentName) ? ' , ' . $user->name : $user->name;
        }

        return $agentName;
    }

    /**
     * Update lead ownership in database
     * @param int $dialingId Dialing ID
     * @param int $leadId Lead ID
     * @param int $newAgentId New agent ID
     */
    private function updateLeadOwnership(int $dialingId, int $leadId, int $newAgentId): void
    {
        DB::table('dialings_leads')
            ->where('dialing_id', $dialingId)
            ->where('lead_id', $leadId)
            ->where('status', 'own')
            ->update(['owned_by_agent_id' => $newAgentId]);
    }

    /**
     * Get owned leads by day (for datatables)
     * @param Request $request HTTP request with date range params
     * @return \Illuminate\Http\JsonResponse
     */
    public function dialingsOwnedLeadsDaywise(Request $request)
    {
        $params = $this->extractListRequestParams($request);

        $isAdmin = $this->getAdminStatus();
        $currentUserId = $this->getCurrentUserId();
        $agentListId = $this->extractAgentListId($request);

        $ownedQuery = $this->buildDaywiseOwnedLeadsQuery($request, $isAdmin, $currentUserId);

        if ($isAdmin) {
            $this->applyAdminDaywiseFilter($ownedQuery, $request);
        } else {
            $this->applyUserDaywiseFilter($ownedQuery, $currentUserId, $request);
        }

        $totalRecords = $ownedQuery->count();
        $filteredRecords = $totalRecords;

        if (!empty($params['searchValue'])) {
            $ownedQuery = $this->applySearchFilter($ownedQuery, $params['searchValue']);
            $filteredRecords = $ownedQuery->count();
        }

        $ownedQuery->orderBy($params['filterOnColumnName'], $params['orderBy'])
            ->offset($params['start'])
            ->limit($params['length']);

        $pageType = 'dialingShow';
        $agentId = $this->getCurrentUserId();
        $ownedContactStatus = self::getOwnedContactStatusOptions();

        return datatables()->of($ownedQuery)
            ->addIndexColumn()
            ->addColumn('business_contacts', function ($row) use ($pageType, $agentListId, $agentId, $ownedContactStatus) {
                return $this->buildContactsColumn($row, $pageType, $agentListId, $agentId, $ownedContactStatus);
            })
            ->rawColumns(['business_contacts'])
            ->addColumn('action', function ($row) {
                return $this->buildAgentLeadsActionColumn($row);
            })
            ->rawColumns(['action'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filteredRecords)
            ->make(true);
    }

    /**
     * Build base query for daywise owned leads
     * @param Request $request HTTP request
     * @param bool $isAdmin Whether user is admin
     * @param int $currentUserId Current user ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildDaywiseOwnedLeadsQuery(Request $request, bool $isAdmin, int $currentUserId)
    {
        return Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
            ->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
            ->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
            ->join('users', 'dialings_leads.owned_by_agent_id', '=', 'users.id')
            ->select('leads.*', 'dialings_leads.ownmarked_at', DB::raw('CONCAT(users.name, " - ", users.email) AS agent_info'));
    }

    /**
     * Apply daywise filter for admin users
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     * @param Request $request HTTP request
     */
    private function applyAdminDaywiseFilter($query, Request $request): void
    {
        $query->distinct('leads.id');

        if (!empty($request->agent_list)) {
            $query->where('dialing_user.user_id', $request->agent_list)
                ->where('dialings_leads.owned_by_agent_id', $request->agent_list);
        } else {
            $query->where('dialing_user.user_id', '>', 0)
                ->where('dialings_leads.owned_by_agent_id', '>', 0);
        }

        $query->where('dialings_leads.status', 'own')
            ->whereBetween('dialings_leads.ownmarked_at', [
                date('Y-m-d H:i:s', strtotime($request->min_time)),
                date('Y-m-d H:i:59', strtotime($request->max_time))
            ]);
    }

    /**
     * Apply daywise filter for non-admin users
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     * @param int $currentUserId Current user ID
     * @param Request $request HTTP request
     */
    private function applyUserDaywiseFilter($query, int $currentUserId, Request $request): void
    {
        $query->distinct('leads.id')
            ->where('dialing_user.user_id', '=', $currentUserId)
            ->where('dialings_leads.owned_by_agent_id', $currentUserId)
            ->where('dialings_leads.status', 'own')
            ->whereBetween('dialings_leads.ownmarked_at', [
                date('Y-m-d H:i:s', strtotime($request->min_time)),
                date('Y-m-d H:i:59', strtotime($request->max_time))
            ]);
    }

    /**
     * Export daywise owned leads to CSV
     * @param Request $request HTTP request with filter params
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function dialingsOwnedLeadsDaywiseExport(Request $request)
    {
        $isAdmin = $this->getAdminStatus();
        $currentUserId = $this->getCurrentUserId();

        $ownedQuery = $this->buildDaywiseOwnedLeadsQuery($request, $isAdmin, $currentUserId);

        if ($isAdmin) {
            $this->applyAdminDaywiseExportFilter($ownedQuery, $request);
        } else {
            $this->applyUserDaywiseExportFilter($ownedQuery, $currentUserId, $request);
        }

        $ownedLeads = $ownedQuery->get();

        return $this->streamCsvResponse($ownedLeads);
    }

    /**
     * Apply export filter for admin users
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     * @param Request $request HTTP request
     */
    private function applyAdminDaywiseExportFilter($query, Request $request): void
    {
        $query->distinct('leads.id');

        if (!empty($request->agent_list)) {
            $query->where('dialing_user.user_id', $request->agent_list)
                ->where('dialings_leads.owned_by_agent_id', $request->agent_list);
        } else {
            $query->where('dialing_user.user_id', '>', 0)
                ->where('dialings_leads.owned_by_agent_id', '>', 0);
        }

        $query->where('dialings_leads.status', 'own')
            ->whereBetween('dialings_leads.ownmarked_at', [
                date('Y-m-d H:i:s', strtotime($request->min_time)),
                date('Y-m-d H:i:59', strtotime($request->max_time)),
            ])
            ->orderBy('ownmarked_at', 'DESC');
    }

    /**
     * Apply export filter for non-admin users
     * @param \Illuminate\Database\Eloquent\Builder $query Query to modify
     * @param int $currentUserId Current user ID
     * @param Request $request HTTP request
     */
    private function applyUserDaywiseExportFilter($query, int $currentUserId, Request $request): void
    {
        $query->distinct('leads.id')
            ->where('dialing_user.user_id', '=', $currentUserId)
            ->where('dialings_leads.owned_by_agent_id', $currentUserId)
            ->where('dialings_leads.status', 'own')
            ->whereBetween('dialings_leads.ownmarked_at', [
                date('Y-m-d H:i:s', strtotime($request->min_time)),
                date('Y-m-d H:i:59', strtotime($request->max_time)),
            ])
            ->orderBy('ownmarked_at', 'DESC');
    }

    /**
     * Stream CSV response for download
     * @param \Illuminate\Collection $ownedLeads Leads to export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function streamCsvResponse($ownedLeads)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=owned_leads_' . date('Y-m-d_H-i-s') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($ownedLeads) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ID', 'Agent Name', 'Business Name', 'Address', 'City', 'Country', 'Own marked on']);

            foreach ($ownedLeads as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->agent_info,
                    $row->name,
                    $row->address1,
                    $row->city,
                    $row->county,
                    date('jS M y, H:i', strtotime($row->ownmarked_at)),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display owned leads list page
     * @return \Illuminate\View\View
     */
    public function ownedleads()
    {
        $agentUsers = $this->buildAgentListWithDefault();
        $isAdminUser = $this->getAdminStatus() ? 1 : 0;
        $vars = ['agentUsers', 'isAdminUser'];

        return view('dialings.owned_list', compact($vars));
    }

    /**
     * Build agent list with default 'Select Agent' option
     * @return array Agent list
     */
    private function buildAgentListWithDefault(): array
    {
        $agents = User::role('Agent')->get();
        $agentUsers = [0 => 'Select Agent'];

        foreach ($agents as $agent) {
            $agentUsers[$agent->id] = $agent->name . '(' . $agent->email . ')';
        }

        return $agentUsers;
    }

    /**
     * Display daywise owned leads page
     * @return \Illuminate\View\View
     */
    public function ownedleadsdaywise()
    {
        $agentUsers = $this->buildAgentListWithDefault();

        // Get current EST time
        $estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
        $estTimeNow = $estTime->format('Y-m-d H:i');

        // Get EST time minus 1 day
        $estTime->modify('-1 day');
        $estTimeNowMinus1Day = $estTime->format('Y-m-d H:i');

        return view('dialings.owned_list_day_wise', compact('agentUsers', 'est_timenow', 'est_timenow_minus1day'));
    }

    /**
     * Create new dialing list
     * @param Request $request HTTP request with dialing params
     * @return array Response with status and message
     */
    public function create(Request $request)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(300);

        // Validate and get agent IDs
        $agentIds = !empty($request->agent_id) ? $request->agent_id : [];

        // Find agent with SMTP configuration
        $mailAgentId = $this->findAgentWithSmtp($agentIds);

        if ($mailAgentId == 0) {
            return ['status' => false, 'message' => 'You do not have SMTP configuration. Please set it up before attempting to create dialing.'];
        }

        // Extract request parameters
        $campaignId = !empty($request->campaign) ? $request->campaign : '';
        $searchFields = !empty($request->search_fields) ? $request->search_fields : '';
        $agentListName = !empty($request->agent_list_name) ? $request->agent_list_name : '';
        $locationLeadsId = !empty($request->location_leads_id) ? json_decode($request->location_leads_id) : '';
        $locationLeadsIdSearch = !empty($request->location_leads_id_search) ? $request->location_leads_id_search : false;

        // Get leads for dialing
        $leadsQuery = $this->leadOutputGetFordialing($locationLeadsIdSearch, $locationLeadsId, $searchFields, $campaignId);
        $leadsCount = $leadsQuery->count();

        if ($leadsCount > 0) {
            $redirectProjectUrl = url('dialings');

            // Dispatch job to create dialing asynchronously
            DialCreateJob::dispatch($agentListName, $agentIds, $locationLeadsIdSearch, $locationLeadsId, $searchFields, $campaignId, $redirectProjectUrl, $mailAgentId);

            return ['status' => true, 'message' => 'Dialing list creation initiated. We will notify you via email upon successful creation.'];
        }

        return ['status' => false, 'message' => 'The filter did not had any leads to add to the dialing list.'];
    }

    /**
     * Find agent with SMTP configuration
     * @param array $agentIds List of agent IDs to check
     * @return int Agent ID with SMTP or 0
     */
    private function findAgentWithSmtp(array $agentIds): int
    {
        $mailAgentId = 0;

        foreach ($agentIds as $id) {
            $smtpSetupData = $this->checkMailConfigurationUserWise($id);
            if ($smtpSetupData != 0) {
                $mailAgentId = $id;
                break;
            }
        }

        return $mailAgentId;
    }

    /**
     * Display dialing view page with leads
     * @param Request $request HTTP request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function view(Request $request)
    {
        $isAdmin = $this->getAdminStatus();
        $currentUserId = $this->getCurrentUserId();

        $viewEditId = !empty($request->viewEditId) ? $request->viewEditId : 0;
        $breadcrumbs = $this->breadcrumbs;
        $showActionLink = $this->showActionLink;
        $showSearchBox = $this->showSearchBox;
        $showPerPage = $this->showPerPage;

        $moduleName = $request->moduleName;
        $modelClass = '\\App\\Model\\' . ucfirst($request->moduleName);
        $model = new $modelClass;

        $tableHeaders = $this->getDefaultTableHeaders();
        $sortColumn = $request->sortColumn ?: '';
        $sortDirection = $request->sortOrder ?: $this->sortDirection;
        $perPage = $request->perPage ?: $this->perPage;
        $currentPage = $request->currentPage ?: $this->currentPage;
        $searchKeyword = $request->keyword ?: '';
        $apiEndpoint = 'getViewApiData';

        if ($request->apiCall) {
            return $this->handleViewApiCall($request, $isAdmin, $currentUserId, $viewEditId, $searchKeyword, $perPage, $currentPage, $sortColumn, $sortDirection, $tableHeaders);
        } else {
            return view('dialings.view', compact('breadcrumbs', 'showActionLink', 'showSearchBox', 'searchKeyword', 'perPage', 'showPerPage', 'sortDirection', 'sortColumn', 'currentPage', 'moduleName', 'tableHeaders', 'apiEndpoint', 'viewEditId'));
        }
    }

    /**
     * Get default table headers for view page
     * @return array Default table headers
     */
    private function getDefaultTableHeaders(): array
    {
        return [
            '0' => ['columnName' => 'leads.id', 'niceName' => 'Id'],
            '1' => ['columnName' => 'leads.name', 'niceName' => 'Business Name'],
            '2' => ['columnName' => 'leads.city', 'niceName' => 'City'],
            '3' => ['columnName' => 'leads.county', 'niceName' => 'County'],
            '4' => ['columnName' => 'leads.unit_count', 'niceName' => 'Unit Counts'],
            '5' => ['columnName' => 'leads.queued_at', 'niceName' => 'Queued On'],
            '6' => ['columnName' => 'leads.no_of_times_contacts_called', 'niceName' => 'Call Count'],
            '7' => ['columnName' => 'leads.contacts', 'niceName' => 'Contacts'],
        ];
    }

    /**
     * Handle API call for view data
     * @param Request $request HTTP request
     * @param bool $isAdmin Whether user is admin
     * @param int $currentUserId Current user ID
     * @param int $viewEditId View/Edit ID
     * @param string $searchKeyword Search keyword
     * @param int $perPage Items per page
     * @param int $currentPage Current page
     * @param string $sortColumn Sort column
     * @param string $sortDirection Sort direction
     * @param array $tableHeaders Table headers
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleViewApiCall(Request $request, bool $isAdmin, int $currentUserId, int $viewEditId, string $searchKeyword, int $perPage, int $currentPage, string $sortColumn, string $sortDirection, array $tableHeaders)
    {
        $dialingId = $agentListId = !empty($request->viewId) ? $request->viewId : 0;

        $dialingQuery = Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
            ->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
            ->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
            ->select('leads.*')
            ->where('dialings_leads.dialing_id', $dialingId);

        if ($isAdmin) {
            $dialingQuery->distinct('leads.id')
                ->where('dialing_user.user_id', '>', 0);
        } else {
            $dialingQuery
                ->where('dialing_user.user_id', '=', $currentUserId)
                ->where('dialings_leads.assigned_to_agent_id', $currentUserId)
                ->where('dialings_leads.status', 'free');
        }

        $fillable = ['leads.id', 'leads.name', 'leads.city', 'leads.unit_count'];

        if (!empty($searchKeyword)) {
            $dialingQuery->where(function ($dialingQuery) use ($searchKeyword, $fillable) {
                $dialingQuery->orWhere(function ($dialingQuery) use ($searchKeyword, $fillable) {
                    foreach ($fillable as $column) {
                        $dialingQuery->orWhere($column, 'like', '%' . $searchKeyword . '%');
                    }
                });
            });
        }

        // Apply sorting
        if (empty($sortColumn)) {
            $dialingQuery
                ->orderBy('leads.queued_at', 'asc')
                ->orderBy('leads.id', 'asc');
        } else {
            $dialingQuery->orderBy($sortColumn, $sortDirection);
        }

        $filteredData = $dialingQuery->get();

        // Manually paginate the filtered data
        $modelData = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredData->forPage($currentPage, $perPage),
            $filteredData->count(),
            $perPage,
            $currentPage
        );

        $pageType = 'dialingShow';

        foreach ($modelData as $key => $singleModel) {
            $row = $singleModel;
            $businessContacts = $singleModel->contacts;
            $view = 'view';
            $modelData[$key]->prospectDetails = view('dialings.partials.contacts-action', compact('view', 'row', 'business_contacts', 'page_type', 'agentlist_id', 'agent_id'))->render();
        }

        return response()->json([
            'response' => $modelData,
            'page' => $modelData,
        ]);
    }

    /**
     * Display dialing list details page
     * @param Request $request HTTP request
     * @param string $encodedId Base64 encoded dialing ID
     * @return \Illuminate\View\View|\Illuminate\RedirectResponse
     */
    public function show(Request $request, string $encodedId)
    {
        $isAdmin = $this->getAdminStatus();

        $agentListId = base64_decode($encodedId);
        $dialing = Dialing::find($agentListId);

        if (!$dialing) {
            return redirect('/dialings');
        }

        $vars = [];
        if ($request->id) {
            $agentListId = $request->id;
            array_push($vars, 'agentListId');
        }

        $agentUsers = $this->buildAgentListWithDefault();

        return view('dialings.show', compact($vars, 'agentUsers', 'dialing', 'isAdmin'));
    }

    /**
     * API endpoint for dialing details (leads)
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dialingDetailsApi(Request $request)
    {
        $params = $this->extractListRequestParams($request);

        $isAdmin = $this->getAdminStatus();
        $currentUserId = $this->getCurrentUserId();

        $dialingId = !empty($request->agentListId) ? base64_decode($request->agentListId) : 0;
        $agentListId = $dialingId;

        $dialingQuery = $this->buildDialingDetailsQuery($dialingId, $currentUserId, $isAdmin);

        if ($isAdmin) {
            $dialingQuery->distinct('leads.id');
            $totalRecords = $dialingQuery->count();
            $filteredRecords = $totalRecords;

            if (!empty($params['searchValue'])) {
                $dialingQuery = $this->applySearchFilter($dialingQuery, $params['searchValue']);
                $filteredRecords = $dialingQuery->count();
            }
            $dialingQuery->orderBy($params['filterOnColumnName'], $params['orderBy'])->offset($params['start'])->limit($params['length']);
        } else {
            $totalRecords = $dialingQuery->count();
            $filteredRecords = $totalRecords;

            if (!empty($params['searchValue'])) {
                $dialingQuery = $this->applySearchFilter($dialingQuery, $params['searchValue']);
                $filteredRecords = $dialingQuery->count();
            }
            $dialingQuery->orderBy($params['filterOnColumnName'], $params['orderBy'])->offset($params['start'])->limit($params['length']);
        }

        $pageType = 'dialingShow';
        $dialingContactStatus = self::getDialingStatusOptions();
        $agentId = $this->getCurrentUserId();

        return datatables()->of($dialingQuery)
            ->addIndexColumn()
            ->addColumn('business_contacts', function ($row) use ($pageType, $agentListId, $agentId, $dialingContactStatus) {
                return $this->buildContactsColumn($row, $pageType, $agentListId, $agentId, $dialingContactStatus);
            })
            ->rawColumns(['business_contacts'])
            ->addColumn('action', function ($row) {
                return $this->buildAgentLeadsActionColumn($row);
            })
            ->rawColumns(['action'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filteredRecords)
            ->make(true);
    }

    /**
     * Build query for dialing details
     * @param int $dialingId Dialing ID
     * @param int $currentUserId Current user ID
     * @param bool $isAdmin Whether user is admin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildDialingDetailsQuery(int $dialingId, int $currentUserId, bool $isAdmin)
    {
        return Lead::join('dialings_leads as dl1', function ($join) use ($dialingId, $currentUserId, $isAdmin) {
            $join->on('leads.id', '=', 'dl1.lead_id')
                ->where('dl1.dialing_id', $dialingId)
                ->where('dl1.status', 'free');
            if ($isAdmin) {
                $join->where('dl1.assigned_to_agent_id', '>', 0);
            } else {
                $join->where('dl1.assigned_to_agent_id', $currentUserId);
            }
        })
            ->leftJoin('dialings_leads as dl2', function ($join) {
                $join->on('leads.id', '=', 'dl2.lead_id')
                    ->where('dl2.status', '=', 'own');
            })
            ->whereNull('dl2.lead_id')
            ->select('leads.id', 'leads.name', 'leads.city', 'leads.county', 'leads.unit_count', 'leads.no_of_times_contacts_called', 'leads.queued_at');
    }

    /**
     * Apply search filter to dialing query
     * @param \Illuminate\Database\Eloquent\Builder $dialingQuery Query to modify
     * @param string $searchValue Search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getSearchData(string $searchValue, $dialingQuery)
    {
        return $dialingQuery->where(function ($dialingQuery) use ($searchValue) {
            $dialingQuery->Where('leads.name', 'like', '%' . $searchValue . '%')
                ->orWhere('leads.city', 'like', '%' . $searchValue . '%')
                ->orWhere('leads.county', 'like', '%' . $searchValue . '%')
                ->orWhere('leads.unit_count', 'like', '%' . $searchValue . '%');
        });
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request HTTP request
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request HTTP request
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id Dialing ID to delete
     * @return \Illuminate\RedirectResponse
     */
    public function destroy(int $id)
    {
        $dialing = Dialing::find($id);

        if ($dialing) {
            if ($dialing->delete()) {
                DB::table('dialings_leads')->where('dialing_id', $id)->delete();
                DB::table('dialing_user')->where('dialing_id', $id)->delete();
                toastr()->success('Dialing List deleted successfully!');
            } else {
                toastr()->error('Something went wrong. Please contact the administrator!');
            }

            return redirect()->back();
        }
    }

    /**
     * Assign agents to dialing list
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request)
    {
        $customStatus = 0;
        $message = 'Required fields are missing. Please contact your administrator!';

        $selectedAgentId = ($request->selected_agent_id > 0) ? $request->selected_agent_id : '';
        $selectedAgentListIds = !empty($request->selected_agent_list_ids) ? $request->selected_agent_list_ids : '';

        if ($selectedAgentId > 0 && count($selectedAgentListIds) > 0) {
            $redirectProjectUrl = url('dialings');
            $mailAgentId = $this->findAgentWithSmtp($selectedAgentId);

            if ($mailAgentId == 0) {
                $message = 'You do not have SMTP configuration. Please set it up before attempting to Reassigning agent to dialing.';
                $customStatus = 1;
            } else {
                AgentReassignJob::dispatch($selectedAgentListIds, $selectedAgentId, $redirectProjectUrl, $mailAgentId);
                $message = "We have initiated the process. Once it's done, we will notify you via email.";
                $customStatus = 1;
            }
        }

        return response()->json([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Reassign agents to leads in a dialing list
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reassignagent(Request $request)
    {
        $customStatus = 0;
        $message = 'Required fields are missing. Please contact your administrator!';

        $agentList = !empty($request->agent_list) ? $request->agent_list : [];
        $selectedValues = !empty($request->selectedValues) ? $request->selectedValues : [];
        $dialingId = !empty($request->dialing_id) ? $request->dialing_id : 0;

        if (count($agentList) > 0 && count($selectedValues) > 0 && !empty($dialingId)) {
            // Reassign leads to agents in round-robin fashion
            $this->reassignLeadsToAgents($dialingId, $agentList, $selectedValues);

            // Update dialing_user associations
            $this->updateDialingUserAssociations($dialingId, $agentList);

            // Clean up unused user associations
            $this->cleanupUnusedUserAssociations($dialingId);

            $message = 'Agent assigned to the selected list successfully!';
            $customStatus = 1;
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Reassign leads to agents using round-robin distribution
     * @param int $dialingId Dialing ID
     * @param array $agentList List of agent IDs
     * @param array $selectedValues List of lead IDs
     */
    private function reassignLeadsToAgents(int $dialingId, array $agentList, array $selectedValues): void
    {
        DB::table('dialings_leads')->where('dialing_id', $dialingId)->where('status', 'free')
            ->whereIn('lead_id', $selectedValues)
            ->orderBy('lead_id')
            ->chunk(1000, function ($freeLeads) use ($dialingId, $agentList) {
                $key = 0;
                $agentListMap = [];

                foreach ($freeLeads as $freeLead) {
                    $agentIndex = $key % count($agentList);
                    $agentValue = $agentList[$agentIndex];
                    $agentListMap[$agentValue][] = $freeLead->lead_id;
                    $key++;
                }

                foreach ($agentListMap as $keyAgent => $valueAgent) {
                    DB::table('dialings_leads')->whereIn('lead_id', $valueAgent)
                        ->where('dialing_id', $dialingId)
                        ->update(['owned_by_agent_id' => 0, 'assigned_to_agent_id' => $keyAgent]);
                }
            });
    }

    /**
     * Update dialing_user associations for agents
     * @param int $dialingId Dialing ID
     * @param array $agentList List of agent IDs
     */
    private function updateDialingUserAssociations(int $dialingId, array $agentList): void
    {
        foreach ($agentList as $agentId) {
            DB::table('dialing_user')
                ->updateOrInsert(
                    ['user_id' => $agentId, 'dialing_id' => $dialingId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
        }
    }

    /**
     * Clean up unused user associations
     * @param int $dialingId Dialing ID
     */
    private function cleanupUnusedUserAssociations(int $dialingId): void
    {
        $dialingUsers = DB::table('dialing_user')
            ->where('dialing_id', $dialingId)
            ->get();

        $deleteUserList = [];
        foreach ($dialingUsers as $dialingUser) {
            $leadCount = DB::table('dialings_leads')->where('assigned_to_agent_id', $dialingUser->user_id)
                ->where('dialing_id', $dialingId)->count();

            if (empty($leadCount)) {
                $deleteUserList[] = $dialingUser->user_id;
            }
        }

        if (count($deleteUserList) > 0) {
            DB::table('dialing_user')
                ->where('dialing_id', $dialingId)
                ->whereIn('user_id', $deleteUserList)
                ->delete();
        }
    }

    /**
     * Change status of dialing list
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function statuschange(Request $request)
    {
        $customStatus = 0;
        $message = 'Required fields are missing. Please contact your administrator!';

        $agentlistId = ($request->agentlist_id > 0) ? $request->agentlist_id : '';
        $currentStatus = !empty($request->current_status) ? $request->current_status : '';

        if ($agentlistId > 0 && $currentStatus) {
            Dialing::where('id', $agentlistId)
                ->update(['status' => $currentStatus]);

            $message = 'Status of dialing list changed successfully!';
            $customStatus = 1;
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Change status of agent's lead
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function agentleadsstatuschange(Request $request)
    {
        $customStatus = 0;
        $message = 'Required fields are missing. Please contact your administrator!';

        $agentlistleadsId = ($request->agentlistleads_id > 0) ? $request->agentlistleads_id : '';
        $currentStatus = !empty($request->current_status) ? $request->current_status : '';

        if ($agentlistleadsId > 0 && $currentStatus > 0) {
            Agentlistlead::where('id', $agentlistleadsId)
                ->update(['status' => $currentStatus]);

            $message = 'Status changed successfully!';
            $customStatus = 1;
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Assign leads to a dialing list
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function assignLeads(Request $request)
    {
        $customStatus = 0;
        $message = 'Required fields are missing. Please contact your administrator!';

        $selectedAgentId = ($request->selected_agent_id > 0) ? $request->selected_agent_id : ('');
        $selectedLeadsId = !empty($request->selected_leads_id) ? $request->selected_leads_id : '';
        $selectedListType = $request->selected_list_type ? $request->selected_list_type : 'new';
        $selectedExistingList = !empty($request->selected_existing_list) ? $request->selected_existing_list : '';
        $currentAgentlistId = ($request->current_agentlist_id > 0) ? $request->current_agentlist_id : ('');

        if ($selectedAgentId > 0 && count($selectedLeadsId) > 0) {
            if ($selectedListType == 'existing') {
                Agentlistlead::whereIn('id', $selectedLeadsId)
                    ->update(['agentlist_id' => $selectedExistingList]);

                $leadsCount = Agentlistlead::where('agentlist_id', $currentAgentlistId)->count();
                Agentlist::where('id', $currentAgentlistId)
                    ->update(['lead_number' => $leadsCount]);

                $existingLeadsCount = Agentlistlead::where('agentlist_id', $selectedExistingList)->count();
                Agentlist::where('id', $selectedExistingList)
                    ->update(['lead_number' => $existingLeadsCount]);

                $message = 'Leads assigned to existing list successfully!';
                $customStatus = 1;
            }
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Get details of agent lists
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function listdetails(Request $request)
    {
        $selectedAgentId = ($request->selected_agent_id > 0) ? $request->selected_agent_id : ('');
        $response = [];

        foreach ($selectedAgentId as $agentId) {
            $response[$agentId] = [];
        }

        $agentsListArr = DB::table('dialing_user')
            ->join('users', 'users.id', '=', 'dialing_user.user_id')
            ->join('dialings', 'dialings.id', '=', 'dialing_user.dialing_id')
            ->whereIn('users.id', $selectedAgentId)
            ->select('users.*', 'dialings.*', 'dialing_user.*')
            ->get();

        foreach ($agentsListArr as $key => $value) {
            $response[$value->user_id][$value->dialing_id] = $value->name;
        }

        return json_encode([
            'status' => '200',
            'response' => $response,
            'listCount' => count($response),
        ]);
    }

    /**
     * Own a lead (for non-admin agents)
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function ownLeads(Request $request)
    {
        $message = 'Admin can assign list to agent but not own a lead!';
        $customStatus = 0;
        $isAdmin = $this->getAdminStatus();

        $selectedLeadsArr = [];
        $selectedLeadIds = ($request->agentlistleads_id > 0) ? array_push($selectedLeadsArr, $request->agentlistleads_id) : [];
        $currentStatus = $request->current_status ? $request->current_status : '';

        if (!$isAdmin) {
            foreach ($selectedLeadsArr as $agentlistleadId) {
                Agentlistlead::where('id', $agentlistleadId)
                    ->update(['agent_id' => auth()->user()->id, 'status' => $currentStatus]);

                if ($currentStatus == 'Own Lead') {
                    $leadIds = Agentlistlead::where('id', $agentlistleadId)->pluck('leads_id')->toArray();
                    $duplicatePrimaryKeys = Agentlistlead::whereIn('leads_id', $leadIds)->pluck('id')->toArray();

                    Agentlistlead::whereIn('id', $leadIds)
                        ->whereIn('id', $duplicatePrimaryKeys)
                        ->where('id', '!=', $agentlistleadId)
                        ->delete();

                    DB::table('agentlist_agentlistlead')
                        ->whereIn('agentlistlead_id', $duplicatePrimaryKeys)
                        ->delete();
                }
            }

            $message = 'Status of lead has been updated successfully!';
            $customStatus = 1;
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Update contact lead status
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function updatecontactleads(Request $request)
    {
        $message = 'Admin cannot change the status of a contact of a business !';
        $customStatus = 0;
        $isAdmin = $this->getAdminStatus();

        $currentStatus = $request->current_status ? $request->current_status : '';
        $contactId = $request->contact_id ? $request->contact_id : '';

        if (!$isAdmin) {
            Contact::where('id', $contactId)
                ->update(['status' => $currentStatus]);
            Calllog::where('contact_id', $contactId)
                ->delete();

            $message = 'Status of contact has been updated successfully!';
            $customStatus = 1;
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
        ]);
    }

    /**
     * Initialize a call for a contact
     * @param Request $request HTTP request
     * @return string JSON encoded response
     */
    public function callinitiated(Request $request)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $message = 'Admin cannot call a lead!';
        $customStatus = 0;
        $isAdmin = $this->getAdminStatus();

        $leadId = ($request->lead_id > 0) ? $request->lead_id : 0;
        $contactId = ($request->contact_id > 0) ? $request->contact_id : 0;
        $dialingId = ($request->dialing_id > 0) ? $request->dialing_id : 0;

        $exceedTimes = Setting::find(1);
        $proceedTimeInMinute = $exceedTimes->proceed_time_in_minute;

        // Check if calling is disabled for this contact
        $timeData = Contact::select('calling_disable_time_in_dialing')->where(['lead_id' => $leadId, 'id' => $contactId])->first();

        if ($timeData) {
            $maxTimeToSend = Carbon::parse($timeData->calling_disable_time_in_dialing);
            $nowAt = Carbon::now();
            $timeDifferenceInMinutes = $nowAt->diffInMinutes($maxTimeToSend, false);

            if ($timeDifferenceInMinutes > 0) {
                return json_encode([
                    'success' => false,
                    'status' => '200',
                    'left_minute' => $timeDifferenceInMinutes,
                    'response' => 'You can not dial right now.',
                ]);
            }
        }

        if (!$isAdmin) {
            $message = 'Parameters missing. Please contact the administrator!';
            if ($leadId > 0 && $contactId > 0) {
                // Update contact with call initiation info
                Contact::updateOrInsert(
                    ['lead_id' => $leadId, 'id' => $contactId],
                    ['agent_call_initiated' => 'yes', 'called_agent_id' => auth()->user()->id, 'calling_disable_time_in_dialing' => Carbon::now()->addMinutes($proceedTimeInMinute)]
                );

                $message = auth()->user()->name . ' has initiated the call of contact: ' . $contactId . ' present in lead: ' . $leadId;

                // Log the agent action
                AgentLog::updateOrInsert(
                    ['lead_id' => $leadId, 'contact_id' => $contactId, 'user_id' => auth()->user()->id],
                    ['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $leadId, 'contact_id' => $contactId, 'status' => 'call_initiated']
                );

                // Trigger websocket event
                $clickDetails = json_encode([
                    'agent_id' => auth()->user()->id,
                    'contact_id' => $contactId,
                    'lead_id' => $leadId,
                    'dialing_id' => $dialingId,
                ]);
                event(new \App\Events\LeadclickedWebsocket($clickDetails));

                $message = 'Status updated successfully!';
                $customStatus = 1;
            }
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customStatus,
            'isAdmin' => $isAdmin,
        ]);
    }
}
