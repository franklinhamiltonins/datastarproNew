<?php

namespace App\Http\Controllers;

use App\Model\ContactStatus;
use App\Model\LeadSource;
use App\Model\User;
use App\Services\GetLangLongGoogleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $showActionLink = true;

    public $showSearchBox = true;

    public $showPerPage = true;

    public $perPage = 25;

    public $currentPage = 1;

    public $sortDirection = 'desc';

    public $sortColumn = '';

    public $apiEndpoint = 'getApiData';

    public $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => '/dashboard'],
    ];

    // Example usage:
    // echo agentsDatatableFilter("Enter search term...");

    private static function getContactList()
    {
        // if value
        return ContactStatus::select('id', 'name', 'false_status', 'display_in_pipedrive')
            ->where(function ($query) {
                $query->whereNull('special_marker')
                    ->orWhere('special_marker', 3);
            })
            ->get();
    }

    public static function getContactStatusOptions()
    {
        return self::getContactList();
    }

    private static $contactOwnedStatusMapping = [
        7 => 7,
        14 => 14,
        6 => 6,
    ];

    public static $leadTableHeadingName = [
        '' => 'Select Column',
        'Frequently Used' => [
            'name' => 'Business Name',
            'c_full_name' => 'Contact Full Name',
            'c_first_name' => 'Contact First Name',
            'c_last_name' => 'Contact Last Name',
            'c_phone' => 'Contact Phone',
            'campaign_date' => 'Last Campaign Date',
            'lead_source' => 'Lead Source',
        ],
        'Other - Lead' => [
            'pipeline_agent_id' => 'Assigned Agent',
            'city' => 'Business City',
            'county' => 'Business County',
            'address1' => 'Business Address 1',
            'address2' => 'Business Adress 2',
            'state' => 'Business State',
            'type' => 'Business Type',
            'unit_count' => 'Business Unit Count',
            'zip' => 'Business Zip',
            'crime_insurance' => 'Crime Insurance',
            'CI_ren_month' => 'Crime Insurance Renewal Month',
            'directors_officers' => 'Directors & Officers',
            'DO_ren_month' => 'Directors & Officers Renewal Month',
            'flood' => 'Flood',
            'F_ren_month' => 'Flood General Liability Renewal Month',
            'general_liability' => 'General Liability',
            'GL_ren_month' => 'General Liability Renewal Month',
            'ins_flood' => 'Insurance Flood',
            'ins_prop_carrier' => 'Insurance Property Carrier',
            'insured_amount' => 'Total insured value',
            'manag_company' => 'Management Company',
            'merge_status' => 'Mergeable',
            'premium' => 'Property Insurance Premium',
            'prop_manager' => 'Property Manager',
            'renewal_date' => 'Property Insurance Renewal Date',
            'renewal_month' => 'Property Insurance Renewal Month',
            'sunbiz_registered_address' => 'Registered Agent Address',
            'sunbiz_registered_name' => 'Registered Agent Name',
            'umbrella' => 'Umbrella',
            'U_ren_month' => 'Umbrella Renewal Month',
            'workers_compensation' => 'Workers Compensation',
            'WC_ren_month' => 'Workers Compensation Renewal Month',
            'creation_date' => 'Year Built',
        ],
        'Other - Contact' => [
            'added_by_scrap_apis' => 'Added By Scrap',
            'c_address1' => 'Contact Address 1',
            'c_address2' => 'Contact Adress 2',
            'c_city' => 'Contact City',
            'c_county' => 'Contact County',
            'c_email' => 'Contact Email',
            'c_state' => 'Contact State',
            'c_title' => 'Contact Title',
            'c_zip' => 'Contact Zip',
        ],
    ];

    public static function getOwnedContactStatusOptions()
    {
        return ContactStatus::where('status_type', 2)
            ->pluck('id', 'id')
            ->toArray();

    }

    public static function getLeadSource()
    {
        return LeadSource::where('status', 1)
            ->pluck('name', 'id')
            ->toArray();

    }

    private static function contactDialingStatusMapping()
    {
        return ContactStatus::where(function ($query) {
            $query->where('status_type', 1)
                ->orWhere('false_status', 1);
        })->pluck('id', 'id')->toArray();
    }

    public static function getagentList()
    {
        $agentUsers = [];
        $agents = User::select('users.id', 'users.name', 'users.email')->role(['Agent', 'Service & Agent'])->get();
        foreach ($agents as $key => $agent) {
            $agentUsers[$agent->id] = $agent->name.' ('.$agent->email.')';
        }

        return $agentUsers;
    }

    public static function getAllAccountIdsForManager($managerId)
    {
        $user = auth()->user();
        $accounts = [$managerId];

        $user = User::where('id', $managerId)->first();

        if ($user) {
            $agents = $user->managerTeamList;
            foreach ($agents as $agent) {
                array_push($accounts, $agent->id);
            }
        }

        return $accounts;
    }

    public static function getagentListBasedonLogin()
    {
        $isAdminUser = auth()->user()->can('all-accounts-list-pipedrive');
        $agentUsers = [];

        if (auth()->user()->hasRole('Manager')) {
            $user = auth()->user();
            $agentUsers[$user->id] = $user->name.' ('.$user->email.')';

            $agents = $user->managerTeamList;
            foreach ($agents as $agent) {
                $agentUsers[$agent->id] = $agent->name.' ('.$agent->email.')';
            }
        } else {
            if ($isAdminUser) {
                $agents = User::select('users.id', 'users.name', 'users.email')->role(['Agent', 'Service & Agent', 'Manager'])->get();
                foreach ($agents as $key => $agent) {
                    $agentUsers[$agent->id] = $agent->name.' ('.$agent->email.')';
                }
            } else {
                $user = auth()->user();
                $agentUsers[$user->id] = $user->name.' ('.$user->email.')';

                $agents = $user->accessibleUsers;
                foreach ($agents as $key => $agent) {
                    $agentUsers[$agent->id] = $agent->name.' ('.$agent->email.')';
                }

            }
        }

        return $agentUsers;
    }

    public static function getDialingStatusOptions()
    {
        return self::contactDialingStatusMapping();
    }

    private static $contactRemovableStatusMapping = [
        2 => 2,
        3 => 3,
        5 => 5,
        4 => 4,
    ];

    public static function getRemovableStatusOptions()
    {
        return self::$contactRemovableStatusMapping;
    }

    public static function getContactStatusLabel($statusCode)
    {
        return isset(self::$contactStatusMapping[$statusCode]) ? self::$contactStatusMapping[$statusCode] : 'Unknown Status';
    }

    public function list(Request $request)
    {
        $breadcrumbs = $this->breadcrumbs;
        $showActionLink = $this->showActionLink;
        $showSearchBox = $this->showSearchBox;
        $showPerPage = $this->showPerPage;

        $hideColumns = $request->hideColumns;
        $moduleName = $request->moduleName;
        $modelClass = '\\App\\Model\\'.ucfirst($request->moduleName);
        $model = new $modelClass;
        $tableHeaders = $request->tableHeaders ? $request->tableHeaders : $model->getTableHeaders();
        $sortColumn = ($request->sortColumn) ? $request->sortColumn : $model->getPrimaryKey();
        $sortDirection = ($request->sortOrder) ? $request->sortOrder : $this->sortDirection;
        $perPage = ($request->perPage) ? $request->perPage : $this->perPage;
        $currentPage = ($request->currentPage) ? $request->currentPage : $this->currentPage;
        $searchKeyword = ($request->keyword) ? $request->keyword : '';

        $apiEndpoint = $this->apiEndpoint;
        if ($request->apiCall) {
            $listoptions = [
                'search' => $searchKeyword, // Search term
                'perPage' => $perPage, // Number of items per page
                'page' => $currentPage, // Current page
                'sortBy' => [$sortColumn => $sortDirection], // Sorting criteria
            ];

            $moduleData = $model->getDataByModel($listoptions);

            return response()->json([

                'response' => $moduleData,
            ]);
        } else {
            return view('modules.list', compact('breadcrumbs', 'showActionLink', 'showSearchBox', 'searchKeyword', 'perPage', 'showPerPage', 'sortDirection', 'sortColumn', 'currentPage', 'moduleName', 'tableHeaders', 'apiEndpoint', 'hideColumns'));
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $moduleName = $request->input('moduleName');
        $modelClass = '\\App\\Model\\'.ucfirst($moduleName);
        $model = $modelClass::find($id);

        if ($model) {
            $model->delete();

            return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }
    }

    public function view(Request $request)
    {
        $moduleName = $request->moduleName;
        $modelClass = '\\App\\Model\\'.ucfirst($moduleName);
        $model = $modelClass::find($request->viewEditId);
        if ($model) {

            return response()->json(['success' => true, 'message' => $model]);
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }
    }

    public function edit(Request $request)
    {
        $moduleName = $request->moduleName;
        $modelClass = '\\App\\Model\\'.ucfirst($moduleName);
        $model = $modelClass::find($request->viewEditId);

        if ($model) {

            return response()->json(['success' => true, 'message' => $model]);
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }
    }

    public function getYearDropdown()
    {
        $startYear = date('Y') + 5;
        $endYear = 1970;

        $years = array_combine(
            range($startYear, $endYear, -1),
            range($startYear, $endYear, -1)
        );

        return ['' => 'Select year'] + $years;

    }

    public function getLatLngFromGoogle($address)
    {
        $googleService = new GetLangLongGoogleService;

        return $googleService->getLatLngFromGoogleService($address);
    }
}
