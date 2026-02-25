<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use App\Jobs\CreateCampaignJob;
use App\Model\Carrier;
use App\Model\County;
use App\Model\InsuranceType;
use App\Model\LeadAdditionalPolicy;
use App\Model\LeadAsanaDetail;
use App\Model\LeadInfoLog;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Filter;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Log;
use App\Model\LeadsModel\Note;
use App\Model\LeadSource;
use App\Model\Rating;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use App\Traits\SMTPRelatedTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Validator;

/**
 * LeadController handles all lead-related operations.
 * Refactored to follow DRY principle with max 3 returns per function.
 */
class LeadController extends Controller
{
    use CommonFunctionsTrait, SMTPRelatedTrait;

    private $leadsFiltered;

    /**
     * Insurance types that have both carriers and ratings.
     */
    private const INSURANCE_TYPES = [
        'Property' => ['carrierVar' => 'carriersWithProperty', 'ratingVar' => 'ratingsWithProperty'],
        'General Liability' => ['carrierVar' => 'carriersWithGeneralLiability', 'ratingVar' => 'ratingsWithGeneralLiability'],
        'Crime Insurance' => ['carrierVar' => 'carriersWithCrimeInsurance', 'ratingVar' => 'ratingsWithCrimeInsurance'],
        'Directors & Officers' => ['carrierVar' => 'carriersWithDirectorOfficor', 'ratingVar' => 'ratingsWithDirectorOfficor'],
        'Umbrella' => ['carrierVar' => 'carriersWithUnbrella', 'ratingVar' => 'ratingsWithUnbrella'],
        'Workers Compensation' => ['carrierVar' => 'carriersWithWorkCompensation', 'ratingVar' => 'ratingsWithWorkCompensation'],
        'Flood' => ['carrierVar' => 'carriersWithFlood', 'ratingVar' => 'ratingsWithFlood'],
        'Difference In Conditions' => ['carrierVar' => 'carriersWithDC'],
        'X-Wind' => ['carrierVar' => 'carriersWithXW'],
        'Equipment Breakdown' => ['carrierVar' => 'carriersWithEB'],
        'Commercial AutoMobile' => ['carrierVar' => 'carriersWithCA'],
        'Marina' => ['carrierVar' => 'carriersWithMarina'],
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete|lead-file-list|lead-action', ['only' => ['index', 'store']]);
        $this->middleware('permission:lead-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:lead-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:lead-delete', ['only' => ['destroy', 'delete_leads', 'remove_leads']]);
        $this->middleware('permission:contact-delete', ['only' => ['destroy', 'delete_leads', 'remove_leads']]);
    }

    /**
     * Display the merge leads view.
     */
    public function merge(Request $request, $slug)
    {
        $leads = Lead::where('lead_slug', $slug)->get();

        if ($leads->count() <= 0) {
            toastr()->error('No mergeable businesses exists for above slug.');

            return back();
        }

        $compareArr = $this->prepareMergeData($leads);

        return view('leads.merge_leads', compact('compareArr'));
    }

    /**
     * Prepare merge data for comparison view.
     *
     * @param \Illuminate\Database\Eloquent\Collection $leads
     * @return array
     */
    private function prepareMergeData($leads)
    {
        $compareArr = [];
        $i = 1;

        foreach ($leads as $lead) {
            $attributes = array_diff_key(
                $lead->getAttributes(),
                array_flip([
                    'latitude', 'longitude', 'is_added_by_bot',
                    'merge_status', 'is_client', 'queued_at',
                    'deleted_at', 'agent_id'
                ])
            );

            $leadData = [];
            foreach ($attributes as $key => $value) {
                $leadData[$key] = $value;
            }

            $leadData['contacts'] = $this->buildMergeContactsHtml($lead, $i);

            $compareArr[] = $leadData;
            $i++;
        }

        return $compareArr;
    }

    /**
     * Build HTML for merge contacts.
     */
    private function buildMergeContactsHtml($lead, $index)
    {
        $contactsHtml = $lead->contacts()
            ->orderBy('contact_slug', 'asc')
            ->pluck('contact_slug')
            ->map(function ($slug) {
                return '<a href="/contacts/merge/'.$slug.'" target="_blank" class="assign_slug">'.$slug.'</a>';
            })
            ->implode(', ');

        return $contactsHtml.' <button class="merge_to_current_lead" id="assign_slug_to_lead'.$index.'">Assign Data</button>';
    }

    /**
     * Complete the merge operation.
     */
    public function completemerge(Request $request)
    {
        $payloadData = $request->all();

        try {
            unset($payloadData['contacts'], $payloadData['mergeable_contacts']);
            $payloadData['merge_status'] = 0;

            // Mark other leads as not mergeable and delete them
            Lead::where('lead_slug', $payloadData['lead_slug'])
                ->where('id', '!=', $payloadData['id'])
                ->update(['merge_status' => 0])
                ->delete();

            // Update the target lead
            Lead::where('id', $payloadData['id'])->update($payloadData);

            return response()->json([
                'status' => true,
                'message' => 'Business merged successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move contacts from one lead to another.
     */
    public function moveContacts(Request $request)
    {
        $mergeLeadIdFrom = $request->mergeLeadIdFrom;
        $mergeLeadIdTo = $request->mergeLeadIdTo;

        try {
            $contactsToUpdate = Contact::where('lead_id', $mergeLeadIdFrom)->get();

            foreach ($contactsToUpdate as $contact) {
                $contact->lead_id = $mergeLeadIdTo;
                $contact->save();
            }

            if ($contactsToUpdate->count() > 0) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Contacts assigned to the selected lead successfully'
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'No contacts found with lead_id = 2'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $tableHeadingName = parent::$leadTableHeadingName;
        $columnsType = Lead::Get_column_type();
        $states = Lead::leadStates();
        $leadFilters = Filter::GetFilters();
        $leadsTypes = $this->getLeadTypes();
        $leadsRenMonths = ['' => 'Select Month', 'empty' => 'Is Empty'] + Lead::leadMonths();
        $leadsStates = ['' => 'Select State', 'empty' => 'Is Empty'] + $states;
        $leadsCounties = $this->getCounties();
        $leadsinsurrance = $this->getInsuranceCarriers();
        $contactsTitle = Lead::contactTitle();
        $agentUsers = $this->getAgentsList();
        $allAccountListPermission = auth()->user()->can('all-accounts-list-pipedrive');
        $leadSource = $this->getLeadSourceOptions();
        $googleMapApiKey = env('GOOGLE_MAP_API_KEY');

        $vars = [
            'tableHeadingName', 'columnsType', 'states', 'leadsinsurrance',
            'leadsTypes', 'leadsRenMonths', 'leadsStates', 'leadsCounties',
            'contactsTitle', 'leadFilters', 'googleMapApiKey', 'agentUsers',
            'allAccountListPermission', 'leadSource'
        ];

        if ($request->id) {
            $leadId = $request->id;
            $vars[] = 'leadId';
        }

        return view('leads.index', compact($vars));
    }

    /**
     * Get cached lead types.
     *
     * @return array
     */
    private function getLeadTypes()
    {
        return Cache::rememberForever('lead_types_list', function () {
            return Lead::select('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type', 'type')
                ->all();
        });
    }

    /**
     * Get counties list.
     *
     * @return array
     */
    private function getCounties()
    {
        return Lead::select('county')
            ->distinct()
            ->orderBy('county', 'asc')
            ->pluck('county', 'county')
            ->all();
    }

    /**
     * Get insurance carriers for property type.
     *
     * @return array
     */
    private function getInsuranceCarriers()
    {
        $property = InsuranceType::where('name', 'Property')->first();

        $carriers = optional(
            optional($property)->carriers()
                ->where('status', 1)
                ->orderBy('name')
                ->get()
        )->pluck('name', 'name')->all();

        return ['' => 'Select Insurance Property Carrier'] + $carriers;
    }

    /**
     * Get agents list for dropdown.
     *
     * @return array
     */
    private function getAgentsList()
    {
        $agents = User::select('users.id', 'users.name', 'users.email')
            ->role(['Agent', 'Service & Agent', 'Manager'])
            ->get();

        $agentUsers = [0 => 'Select Agent'];

        foreach ($agents as $agent) {
            $agentUsers[$agent->id] = $agent->name.' ('.$agent->email.')';
        }

        return $agentUsers;
    }

    /**
     * Get lead source options.
     *
     * @return array
     */
    private function getLeadSourceOptions()
    {
        return LeadSource::where('status', 1)->select('id', 'name')->get();
    }

    /**
     * Get leads with custom filtering for datatables.
     */
    public function get_custom_leads(Request $request)
    {
        // Extract request inputs
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw = $request->input('draw', 1);

        $orderRequest = $request->input('order')[0] ?? [];
        $columnIndex = $orderRequest['column'] ?? 0;
        $orderBy = $orderRequest['dir'] ?? 'desc';
        $filterOnColumnName = $request->input('columns')[$columnIndex]['data'] ?? 'id';

        $searchValue = $request->input('search')['value'] ?? null;
        $filters = $request->searchFields ?: '';
        $locationLeadsId = $request->locationLeadsId
            ? json_decode($request->locationLeadsId)
            : '';
        $locationLeadsIdFlag = $request->locationLeadsIdSearch ?? false;
        $dialingFiltersClicked = $request->dialingFiltersClicked;
        $columnsType = Lead::Get_column_type();
        $campaignId = $request->campaign ?? '';
        $isAdminUser = auth()->user()->can('agent-create');

        // Extract contact source filter
        $filterContactBy = $this->extractContactSourceFilter($filters);

        // Build base query
        $leadsQuery = $this->buildLeadsQuery(
            $locationLeadsIdFlag,
            $locationLeadsId,
            $filters,
            $columnsType,
            $campaignId
        );

        // Apply custom lead filters
        $leadsQuery = filter_leads($leadsQuery, $filters, $columnsType, $campaignId);

        // Handle dialing screen or normal listing
        if ($dialingFiltersClicked) {
            $result = $this->handleDialingScreen(
                $leadsQuery,
                $searchValue,
                $start,
                $length,
                $filterOnColumnName,
                $orderBy
            );
        } else {
            $result = $this->handleNormalListing(
                $leadsQuery,
                $searchValue,
                $start,
                $length,
                $filterOnColumnName,
                $orderBy
            );
        }

        // Return datatable response
        return $this->buildDatatableResponse(
            $result['leads'],
            $result['totalRecords'],
            $result['filteredRecords'],
            $draw,
            $filterContactBy,
            $isAdminUser
        );
    }

    /**
     * Extract contact source filter from filters array.
     */
    private function extractContactSourceFilter($filters)
    {
        if (empty($filters)) {
            return '';
        }

        foreach ((array) $filters as $group) {
            foreach ($group as $item) {
                if (! empty($item['s_name']) && $item['s_name'] === 'added_by_scrap_apis') {
                    return $item['s_val'];
                }
            }
        }

        return '';
    }

    /**
     * Build leads query based on filters.
     */
    private function buildLeadsQuery($locationLeadsIdFlag, $locationLeadsId, $filters, $columnsType, $campaignId)
    {
        if ($locationLeadsIdFlag) {
            return Lead::with('contacts')->whereIn('id', $locationLeadsId);
        }

        if ($filters && isset($filters[0])) {
            return $this->buildDistanceFilterQuery($filters, $columnsType);
        }

        return Lead::with('contacts');
    }

    /**
     * Build query with distance filter.
     */
    private function buildDistanceFilterQuery($filters, $columnsType)
    {
        $distanceFilter = $filters[0];
        $addressText = $distanceFilter[0]['address_text'] ?? '';
        $distanceOp = $distanceFilter[0]['distance_op'] ?? '';
        $distance = $distanceFilter[0]['distance'] ?? '';
        $distanceCheckbox = $distanceFilter[0]['distance_query_selection_checkbox'] ?? 'false';
        $leadBusinessNamesSearch = $distanceFilter[0]['lead_business_names_search'] ?? '';
        $leadBusinessNameSearchId = $distanceFilter[0]['lead_business_name_search_id'] ?? 0;

        // Use saved lead's address
        if ($distanceCheckbox == true && $leadBusinessNamesSearch && $leadBusinessNameSearchId > 0) {
            $lead = Lead::find($leadBusinessNameSearchId);
            if ($lead) {
                $addressText = trim(($lead->address1 ?? '').' '.($lead->address2 ?? ''));
            }
        }

        // Get latitude/longitude
        $latLong = parent::getLatLngFromGoogle($addressText);

        if (is_null($latLong['lat']) && is_null($latLong['long'])) {
            return ['status' => false, 'message' => 'Please add valid address'];
        }

        // Remove distance filter from remaining filters
        $filters = array_slice($filters, 1, null, true);

        $latitude = $latLong['lat'];
        $longitude = $latLong['long'];

        // Build base table
        $table = Lead::with('contacts');

        // Apply distance filter
        if ($distanceOp && $distance) {
            $table->whereIn('id', function ($query) use ($latitude, $longitude, $distanceOp, $distance) {
                $query->from('leads')->selectRaw('id')->whereRaw(
                    "SQRT(
                        POW(69.1 * (latitude - $latitude), 2) +
                        POW(69.1 * ($longitude - longitude) * COS(latitude / 57.3), 2)
                    ) $distanceOp $distance"
                );
            });
        }

        return $table;
    }

    /**
     * Handle dialing screen specific logic.
     */
    private function handleDialingScreen($leadsQuery, $searchValue, $start, $length, $filterOnColumnName, $orderBy)
    {
        $ownedLeads = DB::table('dialings_leads')
            ->where('status', 'owned')
            ->where('owned_by_agent_id', '>', 0)
            ->pluck('lead_id');

        $dialingContactStatus = self::getDialingStatusOptions();

        $leadsQuery->where('is_client', 0);
        $leadsQuery->whereHas('contacts', function ($query) use ($dialingContactStatus) {
            $query->where('c_phone', '<>', '')
                ->whereIn('c_status', $dialingContactStatus);
        });

        // Count before search & pagination
        $totalRecords = $filteredRecords = $leadsQuery->count();

        if ($searchValue) {
            $leadsQuery = $this->get_search_data($searchValue, $leadsQuery);
            $filteredRecords = $leadsQuery->count();
        }

        $leads = $leadsQuery
            ->select($this->getLeadSelectColumns())
            ->with('ownedAgent:id,name')
            ->orderBy('id', 'desc')
            ->offset($start)
            ->limit($length);

        return [
            'leads' => $leads,
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords
        ];
    }

    /**
     * Handle normal listing logic.
     */
    private function handleNormalListing($leadsQuery, $searchValue, $start, $length, $filterOnColumnName, $orderBy)
    {
        $totalRecords = $leadsQuery->count();

        if ($searchValue) {
            $leadsQuery = $this->get_search_data($searchValue, $leadsQuery);
        }

        $filteredRecords = $leadsQuery->count();

        $leads = $leadsQuery
            ->select($this->getLeadSelectColumns())
            ->with('ownedAgent:id,name')
            ->orderBy($filterOnColumnName, $orderBy)
            ->offset($start)
            ->limit($length);

        return [
            'leads' => $leads,
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords
        ];
    }

    /**
     * Get lead select columns.
     *
     * @return array
     */
    private function getLeadSelectColumns()
    {
        return [
            'leads.id', 'leads.type', 'leads.name', 'leads.creation_date',
            'leads.address1', 'leads.city', 'leads.state', 'leads.zip',
            'leads.county', 'leads.unit_count', 'leads.renewal_month',
            'leads.latitude', 'leads.longitude', 'leads.lead_slug',
            'leads.is_added_by_bot', 'leads.merge_status',
            'leads.sunbiz_registered_name', 'leads.sunbiz_registered_address',
            'leads.pipeline_agent_id'
        ];
    }

    /**
     * Build datatable response.
     */
    private function buildDatatableResponse($leads, $totalRecords, $filteredRecords, $draw, $filterContactBy, $isAdminUser)
    {
        return datatables()->of($leads)
            ->addColumn('owned_agent_name', fn ($lead) => $lead->ownedAgent->name ?? '')
            ->addIndexColumn()
            ->editColumn('creation_date', fn ($row) => $row->creation_date
                ? date('m/d/Y', strtotime($row->creation_date))
                : null
            )
            ->editColumn('renewal_date', fn ($row) => $row->renewal_date
                ? date('m/d/Y', strtotime($row->renewal_date))
                : null
            )
            ->addColumn('contacts', fn (Lead $lead) => view('leads.partials.contact-phone', [
                'lead' => $lead,
                'filter_contact_by' => $filterContactBy
            ]))
            ->addColumn('action', function ($row) use ($isAdminUser) {
                return view('leads.partials.lead-buttons-actions', [
                    'editLead' => 'lead-edit',
                    'deleteLead' => 'lead-delete',
                    'crudRoutePart' => 'lead',
                    'row' => $row,
                    'is_admin_user' => $isAdminUser,
                ]);
            })
            ->rawColumns(['action'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filteredRecords)
            ->make(true);
    }

    /**
     * Search leads with given search value.
     */
    public function get_search_data($searchValue, $leadsQuery)
    {
        $columnsToSearch = [
            'leads.type', 'leads.name', 'leads.creation_date', 'leads.city',
            'leads.address1', 'leads.state', 'leads.zip', 'leads.county',
            'leads.unit_count', 'leads.latitude', 'leads.longitude',
            'leads.renewal_month', 'leads.lead_slug', 'leads.sunbiz_registered_name',
            'leads.sunbiz_registered_address'
        ];

        return $this->search($leadsQuery, $searchValue, $columnsToSearch);
    }

    /**
     * Get leads by zip codes with geocoding.
     */
    public function zipCodeLeads(Request $request)
    {
        $geocoded = [];
        $zips = DB::table('leads')->select('zip')->distinct()->get();

        if ($zips->count() > 0) {
            $geocoded = $this->batchGeocodeZips($zips);
        }

        return $geocoded;
    }

    /**
     * Batch geocode zip codes for better performance.
     */
    private function batchGeocodeZips($zips)
    {
        $geocoded = [];
        $apiKey = env('GOOGLE_MAP_API_KEY');

        // Process in batches of 10 to avoid rate limiting
        $zipChunks = $zips->chunk(10);

        foreach ($zipChunks as $chunk) {
            foreach ($chunk as $zipCode) {
                if (! empty($zipCode->zip)) {
                    $geocoded[$zipCode->zip] = $this->geocodeZip($zipCode->zip, $apiKey);
                }
            }

            // Small delay between batches to respect API limits
            usleep(100000); // 100ms delay
        }

        return $geocoded;
    }

    /**
     * Geocode a single zip code.
     */
    private function geocodeZip($zip, $apiKey)
    {
        $serviceUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=$zip&sensor=false&key=$apiKey";
        $resultString = file_get_contents($serviceUrl);
        $result = json_decode($resultString, true);

        if (array_key_exists('status', $result) && $result['status'] == 'OK') {
            return $result['results'][0]['geometry']['location'];
        }

        return $zip.' not found';
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $encrptId)
    {
        $id = base64_decode($encrptId);
        $lead = Lead::find($id);

        if (! $lead) {
            toastr()->error("This Lead doesn't exist");

            return redirect('/leads');
        }

        $data = $this->getPaginatedCampaigns($lead);
        $actions = $this->getPaginatedActions($lead);
        $logs = $lead->logs->sortByDesc('id');
        $notes = $this->getNotes($lead->id);
        $contacts = $lead->contacts;
        $contactsFullNames = $this->buildContactsDropdown($contacts);

        // Static form data
        $campaigns = $lead->campaigns->sortByDesc('id');
        $states = Lead::leadStates();
        $counties = Lead::leadCounties();
        $months = Lead::leadMonths();
        $roofCovering = Lead::leadRoofCovering();
        $roofGeometry = Lead::leadRoofGeometry();
        $years = parent::getYearDropdown();

        $statusOptions = self::getContactStatusOptions();

        // Renewal details
        $renewalData = $this->getRenewalData($id);
        $renewedLead = $renewalData['renewed_lead'];
        $previousLead = $renewalData['previous_lead'];
        $previousLeadDateList = $renewalData['previous_lead_date_list'];
        $previousLeadPolicyList = $renewalData['previous_lead_policy_list'];

        // Additional Policy
        $additonalPolicy = $lead->leadAdditionalpolicy()->get();

        // Return view
        return view('leads.show', compact(
            'lead', 'statusOptions', 'contacts', 'logs', 'contactsFullNames',
            'notes', 'data', 'actions', 'campaigns', 'states', 'counties',
            'months', 'years', 'roofGeometry', 'roofCovering', 'additonalPolicy',
            'renewedLead', 'previousLead', 'previousLeadDateList',
            'previousLeadPolicyList'
        ));
    }

    /**
     * Get paginated campaigns for a lead.
     */
    private function getPaginatedCampaigns($lead)
    {
        return $lead->campaigns()
            ->select('id', 'name', 'status', 'campaign_date')
            ->orderBy('id', 'DESC')
            ->paginate(10, ['*'], 'campaignsShow');
    }

    /**
     * Get paginated actions for a lead.
     */
    private function getPaginatedActions($lead)
    {
        return $lead->actions()
            ->orderBy('id', 'DESC')
            ->paginate(10, ['*'], 'actionsShow');
    }

    /**
     * Get notes for a lead.
     */
    private function getNotes($leadId)
    {
        return Note::leftJoin('contacts', 'notes.contact_id', '=', 'contacts.id')
            ->select('notes.*', 'contacts.c_full_name as contact_name')
            ->where('notes.lead_id', $leadId)
            ->whereNull('notes.deleted_at')
            ->orderBy('notes.id', 'DESC')
            ->get();
    }

    /**
     * Build contacts dropdown array.
     */
    private function buildContactsDropdown($contacts)
    {
        $contactsFullNames = ['' => 'Select Contact'];

        foreach ($contacts as $ct) {
            $contactsFullNames[$ct->id] = $ct->c_first_name.' '.$ct->c_last_name;
        }

        $contactsFullNames['other'] = 'Other';

        return $contactsFullNames;
    }

    /**
     * Get renewal data for a lead.
     *
     * @param int $leadId
     * @return array
     */
    private function getRenewalData($leadId)
    {
        $renewedLead = 0;
        $previousLead = [];
        $previousLeadDateList = [];
        $previousLeadPolicyList = [];

        $leadAsanaDetail = LeadAsanaDetail::select('renewed_lead')
            ->where('lead_id', $leadId)
            ->first();

        if ($leadAsanaDetail && $leadAsanaDetail->renewed_lead) {
            $renewedLead = $leadAsanaDetail->renewed_lead;

            // Previous lead main data
            $leadInfoLog = LeadInfoLog::where('lead_id', $leadId)
                ->where('table_name', 'Lead')
                ->select('data')
                ->orderBy('renewal_date', 'DESC')
                ->first();

            if ($leadInfoLog) {
                $previousLead = $this->rawRefreshLeadData(json_decode($leadInfoLog->data));
            }

            // Previous lead additional policy data
            $leadPolicyLog = LeadInfoLog::where('lead_id', $leadId)
                ->where('table_name', 'LeadAdditionalPolicy')
                ->select('data')
                ->orderBy('renewal_date', 'DESC')
                ->first();

            if ($leadPolicyLog) {
                $previousLeadPolicyList = $this->rawRefreshAdditionalLeadData(
                    json_decode($leadPolicyLog->data)
                );
            }

            // All renewal dates
            $previousLeadDateList = LeadInfoLog::where('lead_id', $leadId)
                ->where('table_name', 'Lead')
                ->select('renewal_date')
                ->orderBy('renewal_date', 'DESC')
                ->get();
        }

        return [
            'renewed_lead' => $renewedLead,
            'previous_lead' => $previousLead,
            'previous_lead_date_list' => $previousLeadDateList,
            'previous_lead_policy_list' => $previousLeadPolicyList,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encrptId)
    {
        $id = base64_decode($encrptId);
        $lead = Lead::find($id);

        if ($lead) {
            $lead->premium = ! empty($lead->premium) ? intval($lead->premium) : $lead->premium;
            $lead->insured_amount = ! empty($lead->insured_amount)
                ? intval($lead->insured_amount)
                : $lead->insured_amount;
        }

        if (! $lead) {
            toastr()->error("This Lead doesn't exist");

            return redirect('/leads');
        }

        $data = $this->getPaginatedCampaigns($lead);
        $actions = $this->getPaginatedActions($lead);
        $logs = $lead->logs->sortByDesc('id');
        $notes = $this->getNotes($id);

        // Static form data
        $contactsTitle = Lead::contactTitle();
        $states = Lead::leadStates();
        $counties = Lead::leadCounties();
        $months = Lead::leadMonths();
        $roofCovering = Lead::leadRoofCovering();
        $roofGeometry = Lead::leadRoofGeometry();
        $years = parent::getYearDropdown();
        $statusOptions = self::getContactStatusOptions();
        $agentList = parent::getagentListBasedonLogin();
        $smtpData = $this->checkMailConfiguration();

        // Contacts list with status
        $contacts = $lead->contacts()->with('contactStatus:id,name')->get();
        $contactsFullNames = $this->buildContactsDropdown($contacts);

        // Campaigns list (non-paginated)
        $campaigns = $lead->campaigns->sortByDesc('id');

        // Insurance types with carriers and ratings
        $insuranceData = $this->getInsuranceTypesWithCarriersAndRatings();
        extract($insuranceData);

        // Lead source
        $leadSource = $this->getLeadSourceOptions();

        // Renewal data
        $renewalData = $this->getRenewalData($id);
        $renewedLead = $renewalData['renewed_lead'];
        $previousLead = $renewalData['previous_lead'];
        $previousLeadDateList = $renewalData['previous_lead_date_list'];
        $previousLeadPolicyList = $renewalData['previous_lead_policy_list'];

        // Additional policy
        $additonalPolicy = $lead->leadAdditionalpolicy()->get();
        $additionalPoliciesCarrier = $this->additionalPoliciesCarrier;

        $varsCompact = [
            'lead', 'contacts', 'statusOptions', 'contactsTitle',
            'states', 'counties', 'months', 'years', 'logs', 'notes',
            'data', 'contactsFullNames', 'actions', 'campaigns',
            'roofGeometry', 'roofCovering', 'smtpData', 'agentList',
            'leadSource', 'renewedLead', 'previousLead',
            'previousLeadDateList', 'additonalPolicy',
            'previousLeadPolicyList', 'carriersWithDC',
            'carriersWithXW', 'carriersWithEB', 'carriersWithCA',
            'carriersWithMarina', 'additionalPoliciesCarrier'
        ];
        $varsCompact = array_merge($varsCompact, array_keys($insuranceData));

        return view('leads.edit', compact(...$varsCompact));
    }

    /**
     * Get insurance types with carriers and ratings.
     *
     * @return array
     */
    private function getInsuranceTypesWithCarriersAndRatings()
    {
        $data = [];

        foreach (self::INSURANCE_TYPES as $typeName => $vars) {
            $type = InsuranceType::where('name', $typeName)->first();

            $data[$vars['carrierVar']] = $type
                ? $type->carriers()
                    ->select('carriers.id', 'carriers.name')
                    ->where('status', 1)
                    ->get()
                : collect();

            if(!empty($vars['ratingVar'])){
                $data[$vars['ratingVar']] = $type
                    ? $type->ratings()
                        ->select('ratings.id', 'ratings.name')
                        ->where('status', 1)
                        ->get()
                    : collect();
            }
        }

        return $data;
    }
    /**
     * Get carrier list for an insurance type via AJAX.
     */
    public function carrierList(Request $request)
    {
        $insurance = InsuranceType::where('name', $request->name)->first();

        $carriers = $insurance
            ? $insurance->carriers()
                ->select('carriers.id', 'carriers.name')
                ->where('status', 1)
                ->get()
            : collect();

        return response()->json(['status' => true, 'carriers' => $carriers]);
    }

    /**
     * Refresh lead data with carrier and rating names.
     */
    public function rawRefreshLeadData($previousLead)
    {
        $carrierFields = [
            'ins_prop_carrier', 'general_liability', 'crime_insurance',
            'directors_officers', 'umbrella', 'workers_compensation',
            'flood', 'difference_in_condition', 'x_wind',
            'equipment_breakdown', 'commercial_automobiles', 'marina',
        ];

        $ratingFields = [
            'rating', 'gl_rating', 'ci_rating', 'do_rating',
            'umbrella_rating', 'wc_rating', 'flood_rating',
        ];

        // Process carrier fields
        foreach ($carrierFields as $field) {
            if (! empty($previousLead->{$field})) {
                $previousLead->{$field} = Carrier::where('id', $previousLead->{$field})
                    ->value('name');
            }
        }

        // Process rating fields
        foreach ($ratingFields as $field) {
            if (! empty($previousLead->{$field})) {
                $previousLead->{$field} = Rating::where('id', $previousLead->{$field})
                    ->value('name');
            }
        }

        return $previousLead;
    }

    /**
     * Refresh additional lead policy data with carrier names.
     */
    public function rawRefreshAdditionalLeadData($previousLeadPolicyList)
    {
        foreach ($previousLeadPolicyList as $keyAddPolicy) {
            if (! empty($keyAddPolicy->carrier)) {
                $keyAddPolicy->carrier = Carrier::where('id', $keyAddPolicy->carrier)
                    ->value('name');
            }
        }

        return $previousLeadPolicyList;
    }

    /**
     * Fetch date-wise older data for a lead.
     */
    public function fetchDateWiseOlderData(Request $request)
    {
        $previousLead = [];
        $previousLeadPolicyList = [];
        $leadFound = 0;

        $leadInfoLog = LeadInfoLog::where('lead_id', $request->lead_id)
            ->where('table_name', 'Lead')
            ->where('renewal_date', $request->renewal_date)
            ->select('data')
            ->first();

        if ($leadInfoLog) {
            $leadFound = 1;
            $previousLead = $this->rawRefreshLeadData(json_decode($leadInfoLog->data));
        }

        $leadPolicyLog = LeadInfoLog::where('lead_id', $request->lead_id)
            ->where('table_name', 'LeadAdditionalPolicy')
            ->select('data')
            ->where('renewal_date', $request->renewal_date)
            ->first();

        if ($leadPolicyLog) {
            $previousLeadPolicyList = $this->rawRefreshAdditionalLeadData(
                json_decode($leadPolicyLog->data)
            );
        }

        return response()->json([
            'status' => true,
            'previous_lead' => $previousLead,
            'previous_lead_policy_list' => $previousLeadPolicyList,
            'lead_found' => $leadFound,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $states = Lead::leadStates();
        $counties = Lead::leadCounties();
        $months = Lead::leadMonths();
        $roofCovering = Lead::leadRoofCovering();
        $roofGeometry = Lead::leadRoofGeometry();
        $years = parent::getYearDropdown();

        $leadsinsurrance = $this->getInsuranceCarriers();
        $leadSource = $this->getLeadSourceOptions();

        return view('leads.create', compact(
            'states', 'counties', 'months', 'roofCovering', 'roofGeometry',
            'years', 'leadsinsurrance', 'leadSource'
        ));
    }

    /**
     * Get static insurance carrier options.
     *
     * @return array
     */
    private function getStaticInsuranceCarriers()
    {
        return [
            '' => 'Select Insurance Property Carrier',
            'American Coastal' => 'American Coastal',
            'Heritage' => 'Heritage',
            'SRU / Lloyds of London' => 'SRU / Lloyds of London',
            'QBE' => 'QBE',
            'Catalytic' => 'Catalytic',
            'Arrowhead' => 'Arrowhead',
            'Layered Program/Multiple Carriers' => 'Layered Program/Multiple Carriers',
            'Centauri' => 'Centauri',
            'Avatar' => 'Avatar',
            'IAT/Occidental' => 'IAT/Occidental',
            'Frontline' => 'Frontline',
            'Ventus' => 'Ventus',
            'Velocity Risk Underwriters' => 'Velocity Risk Underwriters',
            'Lloyds Of London/Other' => 'Lloyds Of London/Other',
            'Citizens' => 'Citizens',
            'other' => 'Other',
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make(
            $request->all(),
            Lead::rules(null),
            [],
            Lead::niceNames()
        );

        if ($validator->fails()) {
            toastr()->error(implode('<br>', $validator->errors()->all()));

            return back()->withErrors($validator)->withInput();
        }

        $input = $request->all();

        // Process county
        $input['county_id'] = $this->processCounty($input['county'] ?? null);

        // Process address - lat/long
        $input = $this->processAddressLatLng($input);

        // Generate slug and check uniqueness
        $validationResult = $this->validateAndGenerateSlug($input, null);

        if ($validationResult['error']) {
            toastr()->error(implode('<br>', $validationResult['message']));

            return back()->withErrors($validator)->withInput();
        }

        $input['lead_slug'] = $validationResult['lead_slug'];

        // Create lead
        $lead = Lead::create($input);

        // Create activity log
        create_log($lead, 'Create Lead', '');

        toastr()->success("Lead <b>{$lead->name}</b> created successfully");

        return redirect()->route('leads.update', ['id' => base64_encode($lead->id)]);
    }

    /**
     * Process county and return county_id.
     *
     * @param string|null $countyName
     * @return int|null
     */
    private function processCounty($countyName)
    {
        if (empty($countyName)) {
            return null;
        }

        $county = County::where('name', 'LIKE', "%{$countyName}%")->first();

        return $county ? $county->id : null;
    }

    /**
     * Process address and get lat/lng.
     *
     * @param array $input
     * @return array
     */
    public function processAddressLatLng($input)
    {
        $input['latitude'] = null;
        $input['longitude'] = null;

        if (! empty($input['address1']) || ! empty($input['address2'])) {
            $addressParts = [
                $input['address1'] ?? '',
                $input['address2'] ?? '',
                $input['city'] ?? '',
                $input['state'] ?? '',
                $input['zip'] ?? '',
            ];

            $address = trim(implode(' ', array_filter($addressParts)), ', ');
            $latLong = parent::getLatLngFromGoogle($address);

            if (! empty($latLong['lat']) && ! empty($latLong['long'])) {
                $input['latitude'] = $latLong['lat'];
                $input['longitude'] = $latLong['long'];
            }
        }

        return $input;
    }

    /**
     * Validate and generate slug.
     *
     * @param array $input
     * @param int|null $leadId
     * @return array
     */
    public function validateAndGenerateSlug($input, $leadId)
    {
        $leadSlug = $this->generateSlug([
            $input['type'] ?? '',
            $input['name'] ?? '',
            $input['city'] ?? '',
            $input['zip'] ?? '',
        ]);

        $input['name'] = $this->removeSpecialCharacters($input['name']);

        if (! $leadSlug) {
            return ['error' => false, 'lead_slug' => null];
        }

        $slugExist = $this->checkLeadSlugExistanceWithDistance(
            $leadSlug,
            $input['latitude'],
            $input['longitude'],
            $leadId
        );

        if (! empty($slugExist['existanceCount']) && $slugExist['existanceCount'] > 0) {
            return ['error' => true, 'message' => $slugExist['message'], 'lead_slug' => null];
        }

        return ['error' => false, 'lead_slug' => $leadSlug];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $rules = Lead::rules($id);
        $niceNames = Lead::niceNames();

        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            toastr()->error(implode('<br>', $validator->errors()->all()));

            return back()->withErrors($validator)->withInput();
        }

        $input = $request->all();

        // Validate additional policies
        $validationResult = $this->validateAdditionalPolicies($input);

        if ($validationResult['error']) {
            return back()->withInput();
        }

        // Validate other carrier fields
        $carrierValidation = $this->validateOtherCarrierFields($input);

        if ($carrierValidation['error']) {
            return back()->withInput();
        }

        // Validate other rating fields
        $ratingValidation = $this->validateOtherRatingFields($input);

        if ($ratingValidation['error']) {
            return back()->withInput();
        }

        // Validate ordinance of law
        if ($this->validateOrdinanceOfLaw($input)) {
            return back()->withInput();
        }

        // Generate slug and clean name
        $input = $this->prepareLeadData($input);

        // Check slug existence
        $slugValidation = $this->validateAndGenerateSlug($input, $id);

        if ($slugValidation['error']) {
            toastr()->error(implode('<br>', $slugValidation['message']));

            return back()->withErrors($validator)->withInput();
        }

        $input['lead_slug'] = $slugValidation['lead_slug'];

        // County lookup
        $input['county_id'] = $this->processCounty($input['county'] ?? null);

        // Find lead
        $lead = Lead::find($id);

        if (! $lead) {
            toastr()->error('Something went wrong');

            return back();
        }

        // Process input fields
        $input = $this->processInputFields($input, $request);

        // Convert other carriers/ratings to database entries
        $input = $this->convertOtherValuesToIds($input);

        // Update record
        $lead->update($input);

        // Update client flag for contacts
        $lead->contacts()->update(['c_is_client' => $request->is_client ? true : false]);

        // Process additional policies
        $this->processAdditionalPolicies($request, $id);

        // Update total premium
        $this->leadTotalPremiumUpdate($id);

        // Lead update log
        create_log($lead, 'Edit Lead', '');

        toastr()->success("Lead <b>{$lead->name}</b> updated successfully");

        return back();
    }

    /**
     * Validate additional policies.
     *
     * @param array $input
     * @return array
     */
    private function validateAdditionalPolicies($input)
    {
        $policyIds = $input['policy_id'] ?? [];
        $carriers = $input['carrier'] ?? [];
        $policyTypes = $input['policy_type'] ?? [];

        foreach ($policyIds as $index => $policyId) {
            $carrierOtherKey = "carrier{$index}-other";
            $carrier = ($carriers[$index] === 'other' && ! empty($input[$carrierOtherKey]))
                ? $input[$carrierOtherKey]
                : ($carriers[$index] ?? null);

            if (empty($carrier)) {
                toastr()->error('Additional Policy '.($index + 1)." 'Carrier' has no value.");

                return ['error' => true];
            }

            if (empty($policyTypes[$index])) {
                toastr()->error('Additional Policy '.($index + 1)." 'Policy Type' has no value.");

                return ['error' => true];
            }
        }

        return ['error' => false];
    }

    /**
     * Validate other carrier fields.
     *
     * @param array $input
     * @return array
     */
    private function validateOtherCarrierFields($input)
    {
        $carrierFields = Carrier::leadFieldWithNickName();

        foreach ($carrierFields as $field => $label) {
            if (! empty($input[$field]) && $input[$field] == 'other' && empty($input["{$field}-other"])) {
                toastr()->error("You have selected 'Other' for the {$label} carrier but not provided a value.");

                return ['error' => true];
            }
        }

        return ['error' => false];
    }

    /**
     * Validate other rating fields.
     *
     * @param array $input
     * @return array
     */
    private function validateOtherRatingFields($input)
    {
        $ratingFields = Rating::leadFieldWithNickName();

        foreach ($ratingFields as $field => $label) {
            if (! empty($input[$field]) && $input[$field] == 'other' && empty($input["{$field}-other"])) {
                toastr()->error("You have selected 'Other' for the {$label} but not provided a value.");

                return ['error' => true];
            }
        }

        return ['error' => false];
    }

    /**
     * Validate ordinance of law field.
     *
     * @param array $input
     * @return bool
     */
    private function validateOrdinanceOfLaw($input)
    {
        if (! empty($input['ordinance_of_law']) && $input['ordinance_of_law'] == 'other') {
            if (! isset($input['ordinance_of_law-other']) || $input['ordinance_of_law-other'] < 0) {
                toastr()->error("Please provide 'Ordinance of Law - Other' value.");

                return true;
            }
        }

        return false;
    }

    /**
     * Prepare lead data (slug and name).
     *
     * @param array $input
     * @return array
     */
    private function prepareLeadData($input)
    {
        $leadSlug = $this->generateSlug([
            $input['type'],
            $input['name'],
            $input['city'],
            $input['zip']
        ]);

        $input['name'] = $this->removeSpecialCharacters($input['name']);

        // Geolocation (address -> lat/lng)
        $input['latitude'] = null;
        $input['longitude'] = null;

        if ($input['address1'] || $input['address2']) {
            $formatted = trim(
                ($input['address1'] ?? '').' '.
                ($input['address2'] ?? '').' '.
                ($input['city'] ?? '').', '.
                ($input['state'] ?? '').', '.
                ($input['zip'] ?? '')
            );

            $latLong = parent::getLatLngFromGoogle($formatted);

            if (! is_null($latLong['lat']) && ! is_null($latLong['long'])) {
                $input['latitude'] = $latLong['lat'];
                $input['longitude'] = $latLong['long'];
            }
        }

        $input['lead_slug'] = $leadSlug;

        return $input;
    }

    /**
     * Process input fields (boolean, exclusions).
     *
     * @param array $input
     * @param Request $request
     * @return array
     */
    private function processInputFields($input, $request)
    {
        // Boolean fields
        $input['is_client'] = $request->is_client ? true : false;

        // Multi-select exclusions
        $input['umbrella_exclusions'] = $request->filled('umbrella_exclusions')
            ? implode(',', $request->input('umbrella_exclusions'))
            : '';

        $input['gl_exclusions'] = $request->filled('gl_exclusions')
            ? implode(',', $request->input('gl_exclusions'))
            : '';

        return $input;
    }

    /**
     * Convert other carrier/rating values to IDs.
     *
     * @param array $input
     * @return array
     */
    private function convertOtherValuesToIds($input)
    {
        // Convert other carriers into DB entries
        $carrierFields = Carrier::leadFieldWithNickName();

        foreach ($carrierFields as $field => $label) {
            if (! empty($input[$field]) && $input[$field] == 'other') {
                $input[$field] = $this->makelogInCarrierTable($input["{$field}-other"], $label);
            }
        }

        // Convert other ratings into DB entries
        $ratingFields = Rating::leadFieldWithNickName();

        foreach ($ratingFields as $field => $label) {
            if (! empty($input[$field]) && $input[$field] == 'other') {
                $input[$field] = $this->makelogInRatingTable($input["{$field}-other"], $label);
            }
        }

        return $input;
    }

    /**
     * Process additional policies.
     *
     * @param Request $request
     * @param int $leadId
     */
    private function processAdditionalPolicies($request, $leadId)
    {
        $policyIds = $request->input('policy_id', []);
        $carriers = $request->input('carrier', []);
        $policyTypes = $request->input('policy_type', []);
        $expiryPremiums = $request->input('expiry_premium', []);
        $policyRenewalDates = $request->input('policy_renewal_date', []);
        $hurricaneDeductibles = $request->input('hurricane_deductible', []);
        $allOtherPerils = $request->input('all_other_perils', []);
        $insuranceCoverage = $request->input('insurance_coverage', []);

        // Remove old additional policies
        LeadAdditionalPolicy::where('lead_id', $leadId)->delete();

        foreach ($policyIds as $index => $policyId) {
            $carrierOtherKey = "carrier{$index}-other";

            $carrier = ($carriers[$index] === 'other' &&
                        $request->has($carrierOtherKey) &&
                        ! empty($request->$carrierOtherKey))
                ? $request->$carrierOtherKey
                : ($carriers[$index] ?? null);

            if ($carriers[$index] === 'other') {
                $carrierId = $this->makelogInCarrierTable($carrier, $policyTypes[$index]);
            } else {
                $carrierId = $carrier;
            }

            if (! empty($carrierId) && ! empty($policyTypes[$index])) {
                $policyData = [
                    'lead_id' => $leadId,
                    'carrier' => $carrierId,
                    'policy_type' => $policyTypes[$index],
                    'expiry_premium' => $expiryPremiums[$index] ?? null,
                    'policy_renewal_date' => $policyRenewalDates[$index] ?? null,
                    'hurricane_deductible' => $hurricaneDeductibles[$index] ?? null,
                    'all_other_perils' => $allOtherPerils[$index] ?? null,
                    'insurance_coverage' => $insuranceCoverage[$index] ?? null,
                ];

                // Update or create
                if (! empty($policyId)) {
                    LeadAdditionalPolicy::withTrashed()
                        ->where('id', $policyId)
                        ->update(array_merge($policyData, ['deleted_at' => null]));
                } else {
                    LeadAdditionalPolicy::create($policyData);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $leadName = $lead->name;

        DB::beginTransaction();

        if (! $this->deleteSingleLead($lead)) {
            DB::rollBack();
            toastr()->error('Something went wrong while deleting the lead.');

            return back();
        }

        DB::commit();

        toastr()->success("Lead <b>{$leadName}</b> deleted successfully!");

        return redirect()->route('leads.index');
    }

    /**
     * Delete a single lead and all related data.
     *
     * @param Lead $lead
     * @return bool
     */
    public function deleteSingleLead(Lead $lead)
    {
        try {
            // Delete related data
            $lead->contacts()->delete();
            $lead->logs()->delete();
            $lead->files()->delete();
            $lead->actions()->delete();
            $lead->notes()->delete();

            // Delete lead
            $lead->delete();

            // Log
            create_log($lead, "Soft Deleted all related data for Lead ID: {$lead->id}", '');

            return true;
        } catch (\Exception $e) {
            \Log::error("Lead Delete Error (ID: {$lead->id}): ".$e->getMessage());

            return false;
        }
    }

    /**
     * Update current insurance for a lead.
     */
    public function update_current_insurance(Request $request, $id)
    {
        $rules = [
            'general_liability' => 'nullable|string|max:191',
            'GL_ren_month' => 'nullable|string|max:191',
            'crime_insurance' => 'nullable|string|max:191',
            'CI_ren_month' => 'nullable|string|max:191',
            'directors_officers' => 'nullable|string|max:191',
            'DO_ren_month' => 'nullable|string|max:191',
            'workers_compensation' => 'nullable|string|max:191',
            'WC_ren_month' => 'nullable|string|max:191',
            'umbrella' => 'nullable|string|max:191',
            'U_ren_month' => 'nullable|string|max:191',
            'flood' => 'nullable|string|max:191',
            'F_ren_month' => 'nullable|string|max:191',
        ];

        $niceNames = [
            'general_liability' => 'General Liability',
            'GL_ren_month' => 'General Liability Renewal Month',
            'crime_insurance' => 'Crime Insurance',
            'CI_ren_month' => 'Crime Insurance Renewal Month',
            'directors_officers' => 'Directors & Officers',
            'DO_ren_month' => 'Directors & Officers Renewal Month',
            'workers_compensation' => 'Workers Compensation',
            'WC_ren_month' => 'Workers Compensation Renewal Month',
            'umbrella' => 'Umbrella',
            'U_ren_month' => 'Umbrella Renewal Month',
            'flood' => 'Flood',
            'F_ren_month' => 'Flood General Liability Renewal Month',
        ];

        // Validate fields using nice name in error messages
        $this->validate($request, $rules, [], $niceNames);

        $input = $request->all();
        $lead = Lead::find($id);
        $lead->update($input);
        $changes = $lead->getChanges();

        if (count($changes) > 0) {
            foreach ($changes as $key => $change) {
                if ($key != 'updated_at') {
                    create_log($lead, 'Update Current Insurance - '.$niceNames[$key], '');
                }
            }
            toastr()->success('Current Insurance for <b>'.$lead->name.'</b> edited successfully');
        } else {
            toastr()->error('No changes to Current Insurance');
        }

        return redirect()->back();
    }

    /**
     * Save campaign with filters.
     */
    public function save_campaign(Request $request)
    {
        $filters = ! empty($request->searchFields1) ? $request->searchFields1 : '';
        $campaignName = ! empty($request->name) ? $request->name : '';
        $campaignId = ! empty($request->campaign) ? $request->campaign : '';
        $locationLeadsId = ! empty($request->locationLeadsId)
            ? json_decode($request->locationLeadsId)
            : '';
        $locationLeadsIdSearch = ! empty($request->locationLeadsIdSearch)
            ? $request->locationLeadsIdSearch
            : false;

        $mailAgentId = auth()->user()->id;
        $mailSmtpCheck = $this->checkMailConfigurationUserWise($mailAgentId);

        if ($mailSmtpCheck == 0) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have SMTP configuration. '.
                    'Please set it up before attempting to create Mailing List.'
            ]);
        }

        CreateCampaignJob::dispatch(
            $filters, $campaignName, $campaignId,
            $locationLeadsId, $locationLeadsIdSearch, $mailAgentId
        );

        return response()->json([
            'status' => true,
            'message' => 'Once the process to create the campaign is initialized, '.
                'we will notify you via email.'
        ]);
    }

    /**
     * Webhook for Ricochet.
     */
    public function getLead(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'phone' => 'required',
        ]);

        if ($request->token == 'GrNB7jTaUIXC9o0EBGOTqB3ME6tQDVLp') {
            $phone = $request->phone;

            if (substr($phone, 0, 1) == 1) {
                $phone = substr($phone, 1);
            }

            // Format phone - remove any chars that are not numbers
            if (preg_match('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', $phone)) {
                $phone = preg_replace('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', '', $phone);
            }

            $contact = Contact::with('leads')->where('c_phone', 'like', $phone)->first();

            if ($contact) {
                return redirect()->route('leads.edit', [
                    'id' => $contact->leads->id,
                    'contact_id' => $contact->id,
                    'contact_phone' => $contact->c_phone
                ]);
            }

            return response()->json(['error' => 'Contact not found ']);
        }

        abort(404);
    }

    /**
     * Display remove bulk leads page.
     */
    public function remove_leads()
    {
        return view('leads.remove_leads');
    }

    /**
     * Delete multiple leads.
     */
    public function delete_leads(Request $request)
    {
        $leadIds = $request->selectedValues;

        if (empty($leadIds)) {
            return response()->json([
                'leadsCount' => 0,
                'message' => 'Please check at least one checkbox to continue.',
            ]);
        }

        $leads = Lead::whereIn('id', $leadIds)->get();

        DB::beginTransaction();

        foreach ($leads as $lead) {
            if (! $this->deleteSingleLead($lead)) {
                DB::rollBack();

                return response()->json([
                    'leadsCount' => 0,
                    'message' => "Failed to delete Lead ID: {$lead->id}",
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'leadsCount' => count($leads),
            'message' => 'Records deleted successfully',
        ]);
    }

    /**
     * Read data from CSV file.
     *
     * @param mixed $csvFile
     * @param string $extension
     * @return array
     */
    private static function readDataFromCsv($csvFile, $extension)
    {
        $fileName = Carbon::now()->format('mdYHisu');

        // If the file is xlsx or xls, convert it to csv
        if ($extension == 'xlsx' || $extension == 'xls') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(
                $extension == 'xlsx' ? 'Xlsx' : 'Xls'
            );
            $reader->setReadDataOnly(true);

            $path = '../storage/app/public/uploads/'.$fileName.'.csv';
            $excel = $reader->load($csvFile);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
            $writer->setUseBOM(false);
            $writer->setOutputEncoding('UTF-8');
            $writer->setEnclosureRequired(false);
            $writer->save($path);

            $csvFile = $path;
        } else {
            Storage::putFileAs('public/uploads', $csvFile, $fileName.'.csv');
        }

        $delimiter = ',';
        $header = null;
        $csvData = [];
        $requiredColumns = [1 => 'Business_Name'];

        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;

                    foreach ($requiredColumns as $req) {
                        if (! in_array($req, $header)) {
                            return ['errors' => 'Column '.$req.' is missing. File was not parsed.'];
                        }
                    }
                } else {
                    if (count($header) > count($row)) {
                        $csvData[] = mb_convert_encoding(
                            array_combine($header, array_pad($row, count($header), '')),
                            'UTF-8',
                            'UTF-8'
                        );
                    } elseif (count($header) < count($row)) {
                        $csvData[] = mb_convert_encoding(
                            array_combine($header, array_slice($row, 0, count($header))),
                            'UTF-8',
                            'UTF-8'
                        );
                    } else {
                        $csvData[] = mb_convert_encoding(
                            array_combine($header, $row),
                            'UTF-8',
                            'UTF-8'
                        );
                    }
                }
            }
            fclose($handle);
        }

        return $csvData;
    }

    /**
     * Add or update filter.
     */
    public function filterStore(Request $request)
    {
        $filters = $request->filters;

        if ($filters && $request->has('save_filter_name') && $request->save_filter_name) {
            $filterId = $request->has('save_filter_id') ? $request->save_filter_id : 0;
            $saveFilterName = $request->has('save_filter_name')
                ? $request->save_filter_name
                : '';

            if ($saveFilterName) {
                $filter = $filterId
                    ? Filter::where([['id', '!=', $filterId], ['name', '=', $saveFilterName]])->first()
                    : Filter::where('name', '=', $saveFilterName)->first();

                if ($filter) {
                    return ['status' => false, 'message' => 'Name for the filter should unique'];
                }
            }

            $filterAddUpdate = [];
            $filterAddUpdate['conditions'] = $filters;
            $filterAddUpdate['name'] = $saveFilterName;

            $filters = json_decode($filters, true);

            if (array_key_exists(0, $filters)) {
                $filterResult = $this->processDistanceFilter($filters);

                if (isset($filterResult['status']) && ! $filterResult['status']) {
                    return $filterResult;
                }

                $filterAddUpdate = array_merge($filterAddUpdate, $filterResult);
            }

            $message = '';
            $id = 0;

            if ($filterId) {
                $filter = Filter::find($filterId);
                $filter->update($filterAddUpdate);
                $id = $filterId;
                $message = 'Filter updated successfully';
            } else {
                $filter = Filter::create($filterAddUpdate);
                $id = $filter->id;
                $message = 'Filter created successfully';
            }

            return ['status' => true, 'message' => $message, 'id' => $id];
        }

        return ['status' => false, 'message' => 'Something went wrong'];
    }

    /**
     * Process distance filter for filter store.
     *
     * @param array $filters
     * @return array
     */
    private function processDistanceFilter($filters)
    {
        $addressText = $filters[0][0]['address_text'] ?? '';
        $distanceOp = $filters[0][0]['distance_op'] ?? '';
        $distanceQuerySelectionCheckbox = $filters[0][0]['distance_query_selection_checkbox'] ?? '';
        $leadBusinessNamesSearch = $filters[0][0]['lead_business_names_search'] ?? '';
        $leadBusinessNameSearchId = $filters[0][0]['lead_business_name_search_id'] ?? 0;

        if ($distanceQuerySelectionCheckbox == 'true' &&
            $leadBusinessNamesSearch &&
            $leadBusinessNameSearchId > 0
        ) {
            $lead = Lead::find($leadBusinessNameSearchId);
            if ($lead) {
                $addressText = ($lead->address1 && $lead->address2)
                    ? $lead->address1.' '.$lead->address2
                    : ($lead->address1 ?? $lead->address2 ?? '');
            }
        }

        $latLong = parent::getLatLngFromGoogle($addressText);

        if (is_null($latLong['lat']) && is_null($latLong['long'])) {
            return ['status' => false, 'message' => 'Please add valid address'];
        }

        return [
            'status' => true,
            'latitude' => $latLong['lat'],
            'longitude' => $latLong['long'],
            'operator' => $distanceOp,
            'address' => $addressText,
            'distance' => $filters[0][0]['distance'] ?? '',
            'is_business_name' => $filters[0][0]['distance_query_selection_checkbox'] ?? '',
            'business_name' => $leadBusinessNamesSearch,
            'business_id' => $leadBusinessNameSearchId,
        ];
    }

    /**
     * Delete filter.
     */
    public function filterDelete(Request $request)
    {
        $filter = Filter::find($request->id);

        if ($filter) {
            $filter->delete();

            return ['status' => true, 'message' => 'Filter deleted successfully'];
        }

        return ['status' => false, 'message' => 'No filter found'];
    }

    /**
     * Custom search leads.
     */
    public function customSearch(Request $request)
    {
        $names = Lead::select('id', 'name')
            ->where('type', $request->type)
            ->where('name', 'like', '%'.$request->term.'%')
            ->whereNotNull('latitude')
            ->get();

        return ['result' => $names];
    }

    /**
     * Get leads from latitude and longitude.
     */
    public function getLeadsIdFromLocation(Request $request)
    {
        $distanceOp = $request->map_marker_distance_op;
        $distance = $request->map_marker_distance;
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $leads = Lead::whereIn('id', function ($query) use ($latitude, $longitude, $distanceOp, $distance) {
            $query->from('leads')
                ->selectRaw('id')
                ->whereRaw(
                    "SQRT(
                        POW(69.1 * (latitude - $latitude), 2) +
                        POW(69.1 * ($longitude - longitude) * COS(latitude / 57.3), 2)
                    ) $distanceOp $distance"
                );
        })->pluck('id')->toArray();

        return ['leads_id' => $leads];
    }

    /**
     * Get all leads location.
     */
    public function getAllLeadsLocation(Request $request)
    {
        $isClientData = $request->is_client;
        $leadQuery = Lead::select('id', 'latitude', 'longitude', 'name', 'is_client')
            ->whereNull('deleted_at');

        if ($isClientData == '1') {
            $leadQuery->where('is_client', 1);
        }

        $leads = $leadQuery->get();
        $locations = [];

        if ($leads) {
            foreach ($leads as $lead) {
                if (is_numeric($lead->latitude) && is_numeric($lead->longitude)) {
                    $locations[] = [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'latitude' => $lead->latitude,
                        'longitude' => $lead->longitude
                    ];
                }
            }
        }

        return ['data' => $locations];
    }
}
