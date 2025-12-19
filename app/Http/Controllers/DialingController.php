<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\LeadsModel\Lead;
use App\Model\Dialing;
use App\Model\Agentlistlead;
use App\Model\LeadsModel\Contact;
use App\Model\User;
use App\Model\Calllog;
use App\Model\Agentlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Model\Setting;
use App\Traits\CommonFunctionsTrait;
use App\Traits\DialRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use App\Jobs\DialCreateJob;
use App\Jobs\AgentReassignJob;
use Symfony\Component\HttpFoundation\StreamedResponse;


class DialingController extends Controller
{
	use CommonFunctionsTrait,DialRelatedTrait,SMTPRelatedTrait;
	private $is_admin = false;


	// dialing index list page start
	public function updateDialingLead()
	{
		$data = DB::select("select leads.id, COUNT(*) as total from `leads` inner join `dialings_leads` on `leads`.`id` = `dialings_leads`.`lead_id` inner join `users` on `dialings_leads`.`owned_by_agent_id` = `users`.`id` where `dialings_leads`.`owned_by_agent_id` > 0 and `dialings_leads`.`status` = 'own' and `leads`.`deleted_at` is null group by `leads`.id having total > 1");

		if(count($data) > 0){
			$updateArr = ['owned_by_agent_id' => 0, 'status' => 'free','ownmarked_at'=>null];
			foreach ($data as $key => $lead) {
				$lead_dialing_id = DB::table('dialings_leads')
			    ->where('lead_id', $lead->id)
			    ->where('status', "own")
			    ->orderByDesc('dialing_id')
			    ->value('dialing_id');

			    if(!empty($lead_dialing_id)){
			    	DB::table('dialings_leads')
				    ->where('lead_id', $lead->id)
				    ->where('status', "own")
				    ->where('dialing_id','!=', $lead_dialing_id)
				    ->update($updateArr);
			    }
			}
		}

		echo "run";
	}
	public function index()
	{
		$is_admin_user = auth()->user()->can('agent-create');
		$agents = User::role('Agent')->get();
		$agent_users = [];
		// $agent_users[0] = 'Select Agent';
		foreach ($agents as $key => $agent) {
			$agent_users[$agent->id] = $agent->name . '(' . $agent->email . ')';
		}
		$vars = array('agent_users', 'is_admin_user');
		$clickdetails = json_encode([
			'agent_id' => 1,
			'contact_id' => 2,
			'lead_id' => 3,
			'dialing_id' => 4,
		]);
		event(new \App\Events\leadclickedWebsocket($clickdetails));


		return view('dialings.index', compact($vars));
	}

	public function dialingListApi(Request $request)
	{
		$is_admin = auth()->user()->can('agent-create');
		$dialing_query = Dialing::select('dialings.id','dialings.referral_marker', 'dialings.name', 'dialings.status');

		$dialings = [];
		if ($is_admin) :
			$dialings = $dialing_query->get();
		else :
			$dialings = $dialing_query->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
				->whereNull('dialings.deleted_at')
				->where('status', 'Active')
				->where('dialing_user.user_id', auth()->user()->id)->get();
		endif;


		foreach ($dialings as $dialing) :
			if(!$is_admin){
				$dialing->agent_name = auth()->user()->name;
				$dialing->agent_ids = auth()->user()->id;
			}
			else{
				$dialing->agent_name = $dialing->users->pluck('name')->implode(', ');
				$dialing->agent_ids = $dialing->users->pluck('id')->implode(', ');
			}

		endforeach;
		return datatables()->of($dialings)
			->addIndexColumn()
			->addColumn('status', function ($row) use ($is_admin) {
				$deleteLead = 'lead-delete';
				$crudRoutePart = 'lead';

				// Create a select dropdown for the 'status' column
				$statusOptions = ['Active', 'Inactive'];
				$selectedStatus = $row->status; // Assuming 'status' is a property in your $agentlists objects
				// if (!$is_admin) {
				// 	return '';
				// }
				$agents_list = 1;

				return view('dialings.partials.status-select', compact('deleteLead', 'crudRoutePart', 'row', 'is_admin', 'statusOptions', 'selectedStatus', 'agents_list'));
			})
			->addColumn('action', function ($row) use ($is_admin) {
				$deleteLead = 'lead-delete';
				$crudRoutePart = 'lead';
				return view('dialings.partials.buttons-actions', compact('deleteLead', 'crudRoutePart', 'row', 'is_admin'));
			})

			->rawColumns(['action'])
			->make(true);
	}

	// dialing index list page end



	public function dialingsOwnedLeads(Request $request)
	{
		$start = $request->input('start', 0);
		$length = $request->input('length', 10); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;

		$is_admin = auth()->user()->can('agent-create');
		$current_user_id = auth()->user()->id;
		$dialing_id = $agentlist_id = (!empty($request->agentlist_id)) ? $request->agentlist_id : 0;
		$owned_query = Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
			// ->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
			// ->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
			->join('users', 'dialings_leads.owned_by_agent_id', '=', 'users.id')
			->select('leads.*','users.name as owned_by','users.id as owned_by_id','dialings_leads.dialing_id');


		if ($is_admin) :
			// $owned_query->distinct('leads.id')
			// 	->where('dialing_user.user_id', '>', 0)
			// 	->where('dialings_leads.owned_by_agent_id', '>', 0)
			// 	->where('dialings_leads.status', 'own')
			// 	->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
			
			$owned_query->distinct('leads.id')
				// ->where('dialing_user.user_id', '>', 0)
				->where('dialings_leads.owned_by_agent_id', '>', 0)
				->where('dialings_leads.status', 'own');
				
			$totalRecords = $owned_query->count();
			$filteredRecords = $owned_query->count();
			if (!empty($search_value)) {
				$owned_query = $this->get_search_data($search_value, $owned_query);
				$filteredRecords = $owned_query->count();
			}
			$owned_query->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
		else :
			// $owned_query
			// 	->where('dialing_user.user_id', '=', $current_user_id)
			// 	->where('dialings_leads.owned_by_agent_id', $current_user_id)
			// 	->where('dialings_leads.status', 'own')
			// 	->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
			$owned_query->distinct('leads.id')
				// ->where('dialing_user.user_id', '=', $current_user_id)
				->where('dialings_leads.owned_by_agent_id', $current_user_id)
				->where('dialings_leads.status', 'own');

			$totalRecords = $owned_query->count();
			$filteredRecords = $owned_query->count();
			if (!empty($search_value)) {
				$owned_query = $this->get_search_data($search_value, $owned_query);
				$filteredRecords = $owned_query->count();
			}
			$owned_query->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
		endif;
		// $totalRecords = $owned_query->count();
		$page_type = 'dialing_show';
		$agent_id = auth()->user()->id;
		$ownedContactStatus = self::getOwnedContactStatusOptions();
		// echo "<pre>";print_r($ownedContactStatus );exit;
		return datatables()->of($owned_query)
			->addIndexColumn()
			->addColumn('business_contacts', function ($row) use ($current_user_id, $page_type, $agentlist_id, $agent_id, $ownedContactStatus) {
				$view = 'view';
				$business_contacts = $row->contacts->filter(function ($contact) use ($ownedContactStatus) {
					return $contact->c_phone != '' && in_array($contact->c_status, $ownedContactStatus);
				});

				return view('dialings.partials.contacts-action', compact('view', 'row', 'business_contacts', 'page_type', 'agentlist_id', 'agent_id'));
			})
			->rawColumns(['business_contacts'])
			->addColumn('action', function ($row) use ($is_admin) {
				$deleteLead = 'lead-delete';
				$crudRoutePart = 'lead';
				return view('dialings.partials.buttons-actions-agentleads', compact('deleteLead', 'crudRoutePart', 'row', 'is_admin'));
			})

			->rawColumns(['action'])
			->setTotalRecords($totalRecords)
			->setFilteredRecords($filteredRecords)
			->make(true);
	}

	public function updateOwner(Request $request,$leadId)
	{
		$checkentry = DB::table('dialing_user')
		->where('dialing_id',$request->reassign_dialing_id)
		->where('user_id',$request->reassign_agent_id)
		->first();
		if(!$checkentry){
			if(!empty($request->force_update)){
				DB::table('dialing_user')
				->insert([
					"dialing_id" => $request->reassign_dialing_id,
					"user_id" => $request->reassign_agent_id,
				]);
			}
			else{
				$agent_name = "";
				$obj = DB::table('dialing_user')
				->where('dialing_id',$request->reassign_dialing_id)
				->join("users",'dialing_user.user_id','=','users.id')
				->select("users.name")
				->get();
				foreach ($obj as  $user) {
					$agent_name .= !empty($agent_name)?  " , ".$user->name : $user->name;
				}
				return response()->json([
		            'status' => false,
		            'agent_name' => $agent_name,
		            'message' => 'This Dialing is not associated with this user, it is associated with '.$agent_name,
		        ],200);
			}
		}

		DB::table('dialings_leads')
		->where('dialing_id',$request->reassign_dialing_id)
		->where('lead_id',$leadId)
		->where('status','own')
		->update(['owned_by_agent_id'=> $request->reassign_agent_id]);

		Contact::where("c_agent_id",$request->old_agent_id)->where("lead_id",$leadId)
		->update(["c_agent_id"=>$request->reassign_agent_id]);

		Lead::where("id",$leadId)->where("pipeline_agent_id",$request->old_agent_id)
		->update(["pipeline_agent_id"=>$request->reassign_agent_id]);

		return response()->json([
            'status' => true,
            'message' => 'Own Lead agent Updated Sucessfully',
        ],200);
	}


	public function dialingsOwnedLeads_daywise(Request $request)
	{
		$start = $request->input('start', 0);
		$length = $request->input('length', 10); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;

		$is_admin = auth()->user()->can('agent-create');
		$current_user_id = auth()->user()->id;
		$dialing_id = $agentlist_id = (!empty($request->agentlist_id)) ? $request->agentlist_id : 0;
		$owned_query = Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
			->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
			->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
			->join('users','dialings_leads.owned_by_agent_id','=','users.id')
			->select('leads.*','dialings_leads.ownmarked_at',DB::raw('CONCAT(users.name, " - ", users.email) AS agent_info'));


		if ($is_admin) :
			// $owned_query->distinct('leads.id')
			// 	->where('dialing_user.user_id', '>', 0)
			// 	->where('dialings_leads.owned_by_agent_id', '>', 0)
			// 	->where('dialings_leads.status', 'own')
			// 	->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
			
			$owned_query->distinct('leads.id');
				if(!empty($request->agent_list)){
					$owned_query->where('dialing_user.user_id',$request->agent_list)
					->where('dialings_leads.owned_by_agent_id',$request->agent_list);
				}
				else{
					$owned_query->where('dialing_user.user_id', '>', 0)
					->where('dialings_leads.owned_by_agent_id', '>', 0);
				}
				
				$owned_query->where('dialings_leads.status', 'own')
				->whereBetween('dialings_leads.ownmarked_at', [date('Y-m-d H:i:s',strtotime($request->min_time)),date('Y-m-d H:i:59',strtotime($request->max_time))]);
				
			$totalRecords = $owned_query->count();
			$filteredRecords = $owned_query->count();
			if (!empty($search_value)) {
				$owned_query = $this->get_search_data($search_value, $owned_query);
				$filteredRecords = $owned_query->count();
			}
			$owned_query->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
		else :
			// $owned_query
			// 	->where('dialing_user.user_id', '=', $current_user_id)
			// 	->where('dialings_leads.owned_by_agent_id', $current_user_id)
			// 	->where('dialings_leads.status', 'own')
			// 	->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
			$owned_query->distinct('leads.id')
				->where('dialing_user.user_id', '=', $current_user_id)
				->where('dialings_leads.owned_by_agent_id', $current_user_id)
				->where('dialings_leads.status', 'own')
				->whereBetween('dialings_leads.ownmarked_at', [date('Y-m-d H:i:s',strtotime($request->min_time)),date('Y-m-d H:i:59',strtotime($request->max_time))]);

			$totalRecords = $owned_query->count();
			$filteredRecords = $owned_query->count();
			if (!empty($search_value)) {
				$owned_query = $this->get_search_data($search_value, $owned_query);
				$filteredRecords = $owned_query->count();
			}
			$owned_query->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
		endif;
		// $totalRecords = $owned_query->count();
		$page_type = 'dialing_show';
		$agent_id = auth()->user()->id;
		$ownedContactStatus = self::getOwnedContactStatusOptions();
		return datatables()->of($owned_query)
			->addIndexColumn()
			->addColumn('business_contacts', function ($row) use ($current_user_id, $page_type, $agentlist_id, $agent_id, $ownedContactStatus) {
				$view = 'view';
				$business_contacts = $row->contacts->filter(function ($contact) use ($ownedContactStatus) {
					return $contact->c_phone != '' && in_array($contact->c_status, $ownedContactStatus);
				});

				return view('dialings.partials.contacts-action', compact('view', 'row', 'business_contacts', 'page_type', 'agentlist_id', 'agent_id'));
			})
			->rawColumns(['business_contacts'])
			->addColumn('action', function ($row) use ($is_admin) {
				$deleteLead = 'lead-delete';
				$crudRoutePart = 'lead';
				return view('dialings.partials.buttons-actions-agentleads', compact('deleteLead', 'crudRoutePart', 'row', 'is_admin'));
			})

			->rawColumns(['action'])
			->setTotalRecords($totalRecords)
			->setFilteredRecords($filteredRecords)
			->make(true);
	}

	public function dialingsOwnedLeads_daywise_export(Request $request)
	{
	    $is_admin = auth()->user()->can('agent-create');
	    $current_user_id = auth()->user()->id;

	    // Build the query
	    $owned_query = Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
	        ->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
	        ->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
	        ->join('users','dialings_leads.owned_by_agent_id','=','users.id')
			->select('leads.*','dialings_leads.ownmarked_at',DB::raw('CONCAT(users.name, " - ", users.email) AS agent_info'));

	    if ($is_admin) {
	        $owned_query->distinct('leads.id');
				if(!empty($request->agent_list)){
					$owned_query->where('dialing_user.user_id',$request->agent_list)
					->where('dialings_leads.owned_by_agent_id',$request->agent_list);
				}
				else{
					$owned_query->where('dialing_user.user_id', '>', 0)
					->where('dialings_leads.owned_by_agent_id', '>', 0);
				}
				
				$owned_query->where('dialings_leads.status', 'own')
				->whereBetween('dialings_leads.ownmarked_at', [
					date('Y-m-d H:i:s',strtotime($request->min_time)),
					date('Y-m-d H:i:59',strtotime($request->max_time))
				])
	            ->orderBy("ownmarked_at", "DESC");
	    } else {
	        $owned_query->distinct('leads.id')
	        	->where('dialing_user.user_id', '=', $current_user_id)
	            ->where('dialings_leads.owned_by_agent_id', $current_user_id)
	            ->where('dialings_leads.status', 'own')
	            ->whereBetween('dialings_leads.ownmarked_at', [
	                date('Y-m-d H:i:s', strtotime($request->min_time)),
	                date('Y-m-d H:i:59', strtotime($request->max_time))
	            ])
	            ->orderBy("ownmarked_at", "DESC");
	    }

	    // Get the actual result from the query
	    $owned_leads = $owned_query->get();

	    // Set headers for file download
	    $headers = [
	        "Content-Type" => "text/csv",
	        "Content-Disposition" => "attachment; filename=owned_leads_" . date('Y-m-d_H-i-s') . ".csv",
	        "Pragma" => "no-cache",
	        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
	        "Expires" => "0",
	    ];

	    // Stream the CSV file as a response
	    $callback = function () use ($owned_leads) {
	        $handle = fopen('php://output', 'w');

	        // Add the CSV headers
	        fputcsv($handle, ['ID','Agent Name', 'Business Name', 'Address', 'City', 'Country', 'Own marked on']);

	        // Add the data rows
	        foreach ($owned_leads as $row) {
	            fputcsv($handle, [
	                $row->id,
	                $row->agent_info,
	                $row->name,
	                $row->address1,
	                $row->city,
	                $row->county,
	                date('jS M y, H:i',strtotime($row->ownmarked_at))
	            ]);
	        }

	        fclose($handle);
	    };

	    return response()->stream($callback, 200, $headers);
	}

	public function ownedleads()
	{
		$agents = User::role('Agent')->get();
		$agent_users = [];
		$agent_users[0] = 'Select Agent';
		foreach ($agents as $key => $agent) {
			$agent_users[$agent->id] = $agent->name . '(' . $agent->email . ')';
		}
		$is_admin_user = auth()->user()->can('agent-create') ? 1:0;
		$vars = array('agent_users','is_admin_user');
		return view('dialings.owned_list', compact($vars));
	}

	public function ownedleadsdaywise()
	{
		$agents = User::role('Agent')->get();
		$agent_users = [];
		$agent_users[0] = 'Select Agent';
		foreach ($agents as $key => $agent) {
			$agent_users[$agent->id] = $agent->name . '(' . $agent->email . ')';
		}
		$estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
		$est_timenow = $estTime->format('Y-m-d H:i');

		$estTime->modify('-1 day'); // Subtract 1 day
		$est_timenow_minus1day = $estTime->format('Y-m-d H:i');
		return view('dialings.owned_list_day_wise', compact('agent_users','est_timenow','est_timenow_minus1day'));
	}


	public function create(Request $request)
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		set_time_limit(300);

		$agent_ids = (!empty($request->agent_id)) ? $request->agent_id : [];
		// echo "<pre>";print_r($agent_id );exit;
		$mail_agent_id = 0;

		foreach ($agent_ids as $id) {
    		$smtp_setup_data = $this->checkMailConfigurationUserWise($id);
    		if ($smtp_setup_data != 0) {
        		$mail_agent_id = $id;
        		break;
    		}
		}

		if($mail_agent_id == 0){
			return array('status' => false, 'message' => 'You do not have SMTP configuration. Please set it up before attempting to create dialing.');
		}

		$campaignId = (!empty($request->campaign)) ? $request->campaign : ('');
		$search_fields = (!empty($request->search_fields)) ? $request->search_fields : ('');
		$agent_list_name = (!empty($request->agent_list_name)) ? $request->agent_list_name : ('');
		$location_leads_id = (!empty($request->location_leads_id)) ? json_decode($request->location_leads_id) : '';
		$location_leads_id_search = (!empty($request->location_leads_id_search)) ? $request->location_leads_id_search : false;

		$leadsQuery = $this->leadoutputget_fordialing($location_leads_id_search,$location_leads_id,$search_fields, $campaignId);


		$leadscount = $leadsQuery->count();

		if ($leadscount > 0) {

			$redirect_project_url = url('dialings');

			DialCreateJob::dispatch($agent_list_name,$agent_ids,$location_leads_id_search,$location_leads_id,$search_fields, $campaignId,$redirect_project_url,$mail_agent_id);

			return array('status' => true, 'message' => 'Dialing list creation initiated. We will notify you via email upon successful creation.');
		}
		return array('status' => false, 'message' => 'The filter did not had any leads to add to the dialing list.');
	}


	public function view(Request $request)
	{
		$is_admin = auth()->user()->can('agent-create');
		$viewEditId = (!empty($request->viewEditId)) ? $request->viewEditId : 0;
		$current_user_id = $agent_id = auth()->user()->id;
		$breadcrumbs = $this->breadcrumbs;
		$showActionLink = $this->showActionLink;
		$showSearchBox = $this->showSearchBox;
		$showPerPage = $this->showPerPage;


		$moduleName = $request->moduleName;
		$modelClass = '\\App\\Model\\' . ucfirst($request->moduleName);
		$model = new $modelClass;
		$tableHeaders = [
			'0' => ['columnName' => 'leads.id', 'niceName' => 'Id'],
			'1' => ['columnName' => 'leads.name', 'niceName' => 'Business Name'],
			'2' => ['columnName' => 'leads.city', 'niceName' => 'City'],
			'3' => ['columnName' => 'leads.county', 'niceName' => 'County'],
			'4' => ['columnName' => 'leads.unit_count', 'niceName' => 'Unit Counts'],
			'5' => ['columnName' => 'leads.queued_at', 'niceName' => 'Queued On'],
			'6' => ['columnName' => 'leads.no_of_times_contacts_called', 'niceName' => 'Call Count'],
			'7' => ['columnName' => 'leads.contacts', 'niceName' => 'Contacts']
		];
		$sortColumn = ($request->sortColumn) ? $request->sortColumn : '';
		$sortDirection = ($request->sortOrder) ? $request->sortOrder : $this->sortDirection;
		$perPage = ($request->perPage) ? $request->perPage : $this->perPage;
		$currentPage = ($request->currentPage) ? $request->currentPage : $this->currentPage;
		$searchKeyword = ($request->keyword) ? $request->keyword : '';
		$apiEndpoint = 'getViewApiData';


		if ($request->apiCall) :
			$dialing_id = $agentlist_id = (!empty($request->viewId)) ? $request->viewId : 0;
			// Instantiate the model
			$listoptions = [
				'search' => $searchKeyword, // Search term
				'perPage' => $perPage, // Number of items per page
				'page' => $currentPage, // Current page
			];

			$dialing_query = Lead::join('dialings_leads', 'leads.id', '=', 'dialings_leads.lead_id')
				->join('dialings', 'dialings.id', '=', 'dialings_leads.dialing_id')
				->join('dialing_user', 'dialings.id', '=', 'dialing_user.dialing_id')
				// Select the columns you want from the leads table
				->select('leads.*')
				->where('dialings_leads.dialing_id', $dialing_id);
			// ->where('dialings_leads.status', '!=', 'uncallable');


			if ($is_admin) :
				$dialing_query->distinct('leads.id')
					->where('dialing_user.user_id', '>', 0);

			else :

				$dialing_query
					->where('dialing_user.user_id', '=', $current_user_id)
					->where('dialings_leads.assigned_to_agent_id', $current_user_id)
					->where('dialings_leads.status', 'free');

			endif;

			$fillable = ['leads.id', 'leads.name', 'leads.city', 'leads.unit_count'];
			if (!empty($listoptions['search'])) {
				$keyword = $listoptions['search'];
				$dialing_query->where(function ($dialing_query) use ($keyword, $fillable) {
					// foreach ($options['search'] as $value) {
					$dialing_query->orWhere(function ($dialing_query) use ($keyword, $fillable) {
						foreach ($fillable as $column) {
							$dialing_query->orWhere($column, 'like', '%' . $keyword . '%');
						}
					});
					// }
				});
			}



			// Handle pagination
			$perPage = isset($listoptions['perPage']) ? $listoptions['perPage'] : 25;
			$page = isset($listoptions['page']) ? $listoptions['page'] : 1;


			if (empty($sortColumn)) :
				$dialing_query
					->orderBy('leads.queued_at', 'asc')
					->orderBy('leads.id', 'asc');
			else :
				$dialing_query->orderBy($sortColumn, $sortDirection);
			endif;
			$filteredData = $dialing_query->get();


			// $filteredData = $dialing_query->get();

			// $leads = $leads->reject(function ($lead) use ($dialingContactStatus) {
			// 	$business_contacts = $lead->contacts->filter(function ($contact) use ($dialingContactStatus) {
			// 		return $contact->c_phone <> '' && in_array($contact->c_status, $dialingContactStatus);
			// 	});
			// 	return $business_contacts->isEmpty();
			// });

			// Manually paginate the filtered data
			$perPage = $listoptions['perPage'];
			$page = $listoptions['page'];

			$modelData = new \Illuminate\Pagination\LengthAwarePaginator(
				$filteredData->forPage($page, $perPage),
				$filteredData->count(),
				$perPage,
				$page
			);

			$page_type = 'dialing_show';


			foreach ($modelData as $key => $singleModel) {
				// dd($singleModel);
				$row = $singleModel;
				$business_contacts = $singleModel->contacts;
				$view = 'view'; // Check the correct path to your view file
				$modelData[$key]->prospect_details = view('dialings.partials.contacts-action', compact('view', 'row', 'business_contacts', 'page_type', 'agentlist_id', 'agent_id'))->render();
			}


			return response()->json([
				'response' => $modelData,
				'page' => $modelData
			]);
		else :
			// dd($tableHeaders);
			return view('dialings.view', compact('breadcrumbs', 'showActionLink', 'showSearchBox', 'searchKeyword', 'perPage', 'showPerPage', 'sortDirection', 'sortColumn', 'currentPage', 'moduleName', 'tableHeaders', 'apiEndpoint', 'viewEditId'));
		endif;
	}

	// dialing list view start
	public function show(Request $request,$encoded_id)
	{
		$is_admin = auth()->user()->can('agent-create');

		$agentlist_id = base64_decode($encoded_id);
		$dialing = Dialing::find($agentlist_id);
		if(!$dialing){
			return redirect('/dialings');
		}
		$vars = array();
		if ($request->id) {
			$agentlist_id = $request->id;
			array_push($vars, 'agentlist_id');
		}
		$agents = User::role('Agent')->get();
		$agent_users = [];
		$agent_users[0] = 'Select Agent';
		foreach ($agents as $key => $agent) {
			$agent_users[$agent->id] = $agent->name . '(' . $agent->email . ')';
		}
		// return view('dialings.show', compact('vars','agent_users'));
		return view('dialings.show', compact($vars,'agent_users','dialing','is_admin'));
	}


	public function dialingDetailsApi(Request $request)
	{
		$start = $request->input('start', 0);
		$length = $request->input('length', 10); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;
		
		$is_admin = auth()->user()->can('agent-create');
		$current_user_id = auth()->user()->id;
		$dialing_id = $agentlist_id = (!empty($request->agentlist_id)) ? $request->agentlist_id : 0;
		$dialing_id = base64_decode($dialing_id);
		// $dialing_query = Lead::join('dialings_leads as dl1', 'leads.id', '=', 'dl1.lead_id')
		//     ->leftJoin('dialings_leads as dl2', function($join) {
		//         $join->on('leads.id', '=', 'dl2.lead_id')
		//              ->where('dl2.status', '=', 'own');
		//     })
		//     ->select('leads.id','leads.name','leads.city','leads.county','leads.unit_count','leads.no_of_times_contacts_called','leads.queued_at')
		//     ->where('dl1.dialing_id', $dialing_id)
		//     ->where('dl1.status', 'free')
		//     ->whereNull('dl2.lead_id');

		$dialing_query = Lead::join('dialings_leads as dl1', function($join) use ($dialing_id, $current_user_id,$is_admin) {
		        $join->on('leads.id', '=', 'dl1.lead_id')
		        ->where('dl1.dialing_id', $dialing_id)
		        ->where('dl1.status', 'free');
		        if ($is_admin){
		        	$join->where('dl1.assigned_to_agent_id', '>', 0);
		        }
		        else{
		        	$join->where('dl1.assigned_to_agent_id', $current_user_id);
		        }  
		    })
		    ->leftJoin('dialings_leads as dl2', function($join) {
		        $join->on('leads.id', '=', 'dl2.lead_id')
		             ->where('dl2.status', '=', 'own');
		    })
		    ->whereNull('dl2.lead_id')
		    ->select('leads.id','leads.name','leads.city','leads.county','leads.unit_count','leads.no_of_times_contacts_called','leads.queued_at');


		if ($is_admin) :
			$dialing_query->distinct('leads.id');
				// ->where('dl1.assigned_to_agent_id', '>', 0);
			$totalRecords = $dialing_query->count();
			$filteredRecords = $totalRecords;

			if (!empty($search_value)) {
				$dialing_query = $this->get_search_data($search_value, $dialing_query);
				$filteredRecords = $dialing_query->count();
			}
			//$dialing_query->orderBy('leads.queued_at', 'asc')->offset($start)->limit($length);
			$dialing_query->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
		else :
			// $dialing_query
			// 	->where('dl1.assigned_to_agent_id', $current_user_id);
			$totalRecords = $dialing_query->count();
			$filteredRecords = $totalRecords;

			if (!empty($search_value)) {
				$dialing_query = $this->get_search_data($search_value, $dialing_query);
				$filteredRecords = $dialing_query->count();
			}
			// $dialing_query->orderBy('leads.queued_at', 'asc')->offset($start)->limit($length);
			$dialing_query->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);

		endif;
		$page_type = 'dialing_show';
		$dialingContactStatus = self::getDialingStatusOptions();
		$agent_id = auth()->user()->id;
		return datatables()->of($dialing_query)
			->addIndexColumn()
			->addColumn('business_contacts', function ($row) use ($current_user_id, $page_type, $agentlist_id, $agent_id, $dialingContactStatus) {
				$view = 'view';
				$business_contacts = $row->contacts->filter(function ($contact) use ($dialingContactStatus) {
					return $contact->c_phone != '' && in_array($contact->c_status, $dialingContactStatus);
				});
				return view('dialings.partials.contacts-action', compact('view', 'row', 'business_contacts', 'page_type', 'agentlist_id', 'agent_id'));
			})
			->rawColumns(['business_contacts'])
			->addColumn('action', function ($row) use ($is_admin) {
				$deleteLead = 'lead-delete';
				$crudRoutePart = 'lead';
				return view('dialings.partials.buttons-actions-agentleads', compact('deleteLead', 'crudRoutePart', 'row', 'is_admin'));
			})

			->rawColumns(['action'])
			->setTotalRecords($totalRecords)
			->setFilteredRecords($filteredRecords)
			->make(true);
	}

	public function get_search_data($search_value, $dialing_query){

		// $columns_to_search = ['leads.name', 'leads.city', 'leads.county', 'leads.unit_count'];
		// return $dialing_query = $this->search($dialing_query,  $search_value, $columns_to_search);
		return $dialing_query->where(function ($dialing_query) use ($search_value) {
			$dialing_query->Where('leads.name', 'like', '%' . $search_value . '%')
				->orWhere('leads.city', 'like', '%' . $search_value . '%')
				->orWhere('leads.county', 'like', '%' . $search_value . '%')
				->orWhere('leads.unit_count', 'like', '%' . $search_value . '%');
		});
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Model\County  $county
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Model\County  $county
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Model\County  $county
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$dialing = Dialing::find($id);
		if ($dialing) {
			if ($dialing->delete()) :
				DB::table('dialings_leads')->where('dialing_id', $id)->delete();
				DB::table('dialing_user')->where('dialing_id', $id)->delete();
				toastr()->success('Dialing List deleted successfully!');
			else :
				toastr()->error('Something went wrong. Please contact the administrator!');
			endif;
			return redirect()->back();
		}
	}

	public function assign(Request $request)
	{
		$custom_status = 0;
		$message = 'Required fields are missing. Please contact your administrator!';
		$selected_agent_id = ($request->selected_agent_id > 0) ? $request->selected_agent_id : '';
		$selected_agent_list_ids = (!empty($request->selected_agent_list_ids)) ? $request->selected_agent_list_ids : '';
		if ($selected_agent_id > 0 && count($selected_agent_list_ids) > 0) :
			// Synchronize the agent lists for all users for each selected_agent_list_id

			$redirect_project_url = url('dialings');


			$mail_agent_id = 0;

			foreach ($selected_agent_id as $id) {
	    		$smtp_setup_data = $this->checkMailConfigurationUserWise($id);
	    		if ($smtp_setup_data != 0) {
	        		$mail_agent_id = $id;
	        		break;
	    		}
			}

			if($mail_agent_id == 0){
				$message = 'You do not have SMTP configuration. Please set it up before attempting to Reassigning agent to dialing.';
				$custom_status = 1;
			}
			else{
				AgentReassignJob::dispatch($selected_agent_list_ids,$selected_agent_id,$redirect_project_url,$mail_agent_id);

				// $message = 'Agent assigned to the selected Dialing list successfully!';
				$message = "We have initiated the process. Once it's done, we will notify you via email.";
				$custom_status = 1;
			}

			
		endif;
		return response()->json([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}

	public function reassignagent(Request $request)
	{
		$custom_status = 0;
		$message = 'Required fields are missing. Please contact your administrator!';
		$agent_list = (!empty($request->agent_list)) ? $request->agent_list : [];
		$selectedValues = (!empty($request->selectedValues)) ? $request->selectedValues : [];
		$dialing_id = (!empty($request->dialing_id)) ? $request->dialing_id : 0;

		if (count($agent_list) > 0 && count($selectedValues) > 0 && !empty($dialing_id)) :
			// Synchronize the agent lists for all users for each selected_agent_list_id
			
				DB::table('dialings_leads')->where('dialing_id', $dialing_id)->where('status', 'free')
				->whereIn('lead_id',$selectedValues)
				->orderBy('lead_id')
				->chunk(1000, function($freeLeads)use ($dialing_id, $agent_list){
					$key = 0;
					$agentlist = [];
					foreach ($freeLeads as $free_lead) {
						$agentIndex = $key % count($agent_list);
						$agentValue = $agent_list[$agentIndex];
						$agentlist[$agentValue][] = $free_lead->lead_id;
						$key++;
					}
					foreach ($agentlist as $keyagent => $valueagent) {
						DB::table('dialings_leads')->whereIn('lead_id',$valueagent)
						->where('dialing_id',$dialing_id)
						->update(['owned_by_agent_id' => 0,  'assigned_to_agent_id' => $keyagent]);
					}
					unset($agentlist);
				});


				foreach ($agent_list as $agent_id) {
					DB::table('dialing_user')
						->updateOrInsert(
							['user_id' => $agent_id, 'dialing_id' => $dialing_id],
							['created_at' => now(), 'updated_at' => now()]
						);
				}

				$dialing_users = DB::table('dialing_user')
				->where('dialing_id',$dialing_id)
				->get();

				$delete_user_list = [];
				foreach ($dialing_users as $dialing_user) {
					if(empty(DB::table('dialings_leads')->where('assigned_to_agent_id',$dialing_user->user_id)
						->where('dialing_id',$dialing_id)->count())){
						array_push($delete_user_list, $dialing_user->user_id);
					}
				}

				if(count($delete_user_list) > 0){
					DB::table('dialing_user')
	                ->where('dialing_id', $dialing_id)
	                ->whereIn('user_id', $delete_user_list)
	                ->delete();
				}

				unset($dialing_users,$delete_user_list);


			$message = 'Agent assigned to the selected list successfully!';
			$custom_status = 1;
		endif;

		return response()->json([
			'status' => true,
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}

	// function to update the status of dialings start
	public function statuschange(Request $request)
	{
		$custom_status = 0;
		$message = 'Required fields are missing. Please contact your administrator!';
		$agentlist_id = ($request->agentlist_id > 0) ? $request->agentlist_id : '';
		$current_status = (!empty($request->current_status)) ? $request->current_status : '';
		if ($agentlist_id > 0 && $current_status) :

			Dialing::where('id', $agentlist_id)
				->update(['status' => $current_status]);

			$message = 'Status of dialing list changed successfully!';
			$custom_status = 1;
		endif;
		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}
	// function to update the status of dialings end

	public function agentleadsstatuschange(Request $request)
	{
		$custom_status = 0;
		$message = 'Required fields are missing. Please contact your administrator!';
		$agentlistleads_id = ($request->agentlistleads_id > 0) ? $request->agentlistleads_id : '';
		$current_status = (!empty($request->current_status)) ? $request->current_status : '';
		if ($agentlistleads_id > 0 && $current_status > 0) :

			Agentlistlead::where('id', $agentlistleads_id)
				->update(['status' => $current_status]);

			$message = 'Status changed successfully!';
			$custom_status = 1;
		endif;
		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}

	public function assignLeads(Request $request)
	{
		$custom_status = 0;
		$message = 'Required fields are missing. Please contact your administrator!';
		$selected_agent_id = ($request->selected_agent_id > 0) ? $request->selected_agent_id : ('');
		$selected_leads_id = (!empty($request->selected_leads_id)) ? $request->selected_leads_id : '';
		$selected_list_type = ($request->selected_list_type) ? $request->selected_list_type : 'new';
		$selected_existing_list = (!empty($request->selected_existing_list)) ? $request->selected_existing_list : '';
		$current_agentlist_id = ($request->current_agentlist_id > 0) ? $request->current_agentlist_id : ('');


		if ($selected_agent_id > 0 && count($selected_leads_id) > 0) :
			if ($selected_list_type == 'new') :

			endif;

			if ($selected_list_type == 'existing') :

				Agentlistlead::whereIn('id', $selected_leads_id)
					->update(['agentlist_id' => $selected_existing_list]);

				$leads_count = Agentlistlead::where('agentlist_id', $current_agentlist_id)->count();
				Agentlist::where('id', $current_agentlist_id)
					->update(['lead_number' => $leads_count]);

				$existing_leads_count = Agentlistlead::where('agentlist_id', $selected_existing_list)->count();
				Agentlist::where('id', $selected_existing_list)
					->update(['lead_number' => $existing_leads_count]);

				$message = 'Leads assigned to existing list successfully!';
				$custom_status = 1;

			endif;

		endif;


		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}

	public function listdetails(Request $request)
	{
		$selected_agent_id = ($request->selected_agent_id > 0) ? $request->selected_agent_id : ('');
		$response = [];
		foreach ($selected_agent_id as $agent_id) {
			$response[$agent_id] = [];
		}


		$agents_list_arr = DB::table('dialing_user')
			->join('users', 'users.id', '=', 'dialing_user.user_id')
			->join('dialings', 'dialings.id', '=', 'dialing_user.dialing_id')
			->whereIn('users.id', $selected_agent_id)
			->select('users.*', 'dialings.*', 'dialing_user.*') // Select the columns you need
			->get();

		foreach ($agents_list_arr as $key => $value) :
			$response[$value->user_id][$value->dialing_id] = $value->name;
		endforeach;




		return json_encode([
			'status' => '200',
			'response' => $response,
			'listCount' => count($response),
		]);
	}

	public function ownLeads(Request $request)
	{
		$message = "Admin can assign list to agent but not own a lead!";
		$custom_status = 0;
		$is_admin = auth()->user()->can('agent-create');
		$selected_leads_arr = [];


		$selected_lead_ids = ($request->agentlistleads_id > 0) ? array_push($selected_leads_arr, $request->agentlistleads_id) : [];
		$current_status = $request->current_status ? $request->current_status : '';
		if (!$is_admin) {

			foreach ($selected_leads_arr as $agentlistlead_id) :
				Agentlistlead::where('id', $agentlistlead_id)
					->update(['agent_id' => auth()->user()->id, 'status' => $current_status]);

				if ($current_status == 'Own Lead') :
					$leads_ids = Agentlistlead::where('id', $agentlistlead_id)->pluck('leads_id')->toArray(); // get leads associated with it



					$duplicate_primarykeys = Agentlistlead::whereIn('leads_id', $leads_ids)->pluck('id')->toArray(); // get leads associated with it
					// dd($duplicate_primarykeys);

					Agentlistlead::whereIn('id', $leads_ids)
						->whereIn('id', $duplicate_primarykeys)
						->where('id', '!=', $agentlistlead_id)
						->delete();

					DB::table('agentlist_agentlistlead')
						->whereIN('agentlistlead_id', $duplicate_primarykeys)
						->delete();
				endif;



			endforeach;

			$message = "Status of lead has been updated successfully!";
			$custom_status = 1;
		}
		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}

	public function updatecontactleads(Request $request)
	{
		$message = "Admin cannot change the status of a contact of a business !";
		$custom_status = 0;
		$is_admin = auth()->user()->can('agent-create');


		$current_status = $request->current_status ? $request->current_status : '';
		$contact_id = $request->contact_id ? $request->contact_id : '';
		if (!$is_admin) {

			Contact::where('id', $contact_id)
				->update(['status' => $current_status]);
			Calllog::where('contact_id', $contact_id)
				->delete();

			$message = "Status of contact has been updated successfully!";
			$custom_status = 1;
		}
		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}

	public function callinitiated(Request $request)
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		$message = "Admin cannot call a lead!";
		$custom_status = 0;
		$is_admin = auth()->user()->can('agent-create');


		$lead_id = ($request->lead_id > 0) ? $request->lead_id : 0;
		$contact_id = ($request->contact_id > 0) ? $request->contact_id : 0;
		$dialing_id = ($request->dialing_id > 0) ? $request->dialing_id : 0;

		$exceed_times = Setting::Find(1);
		$proceed_time_in_minute = $exceed_times->proceed_time_in_minute;

		// backend disabled the button
		$timeData = Contact::select('calling_disable_time_in_dialing')->where(['lead_id' => $lead_id, 'id' => $contact_id])->first();
		$maxTimeToSend = Carbon::parse($timeData->calling_disable_time_in_dialing);
		$nowAt = Carbon::now();
		$timeDifferenceInMinutes = $nowAt->diffInMinutes($maxTimeToSend, false);
			if ($timeDifferenceInMinutes > 0) {
				return json_encode([
					'success' => false,
					'status' => '200',
					'left_minute' => $timeDifferenceInMinutes,
					'response' => "You can not dial right now."
				]);
			} 
		

		if (!$is_admin) {
			$message = "Parameters missing.Please contact the administrator!";
			if ($lead_id > 0 && $contact_id > 0) :

				Contact::updateOrInsert(
					['lead_id' => $lead_id, 'id' => $contact_id],
					['agent_call_initiated' => 'yes', 'called_agent_id' => auth()->user()->id, 'calling_disable_time_in_dialing' => Carbon::now()->addMinutes($proceed_time_in_minute)]
				);
				$message = auth()->user()->name . ' has initiated the call of contact: ' . $contact_id . ' present in lead: ' . $lead_id;
				Agentlog::updateOrInsert(
					['lead_id' => $lead_id, 'contact_id' => $contact_id, 'user_id' => auth()->user()->id],
					['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $lead_id, 'contact_id' => $contact_id, 'status' => 'call_initiated']
				);
				$clickdetails = json_encode([
					'agent_id' => auth()->user()->id,
					'contact_id' => $contact_id,
					'lead_id' => $lead_id,
					'dialing_id' => $dialing_id,
				]);
				event(new \App\Events\leadclickedWebsocket($clickdetails));


				$message = "Status updated successfully!";
				$custom_status = 1;
			endif;
		}

		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
			'is_admin' => $is_admin
		]);
	}
}
