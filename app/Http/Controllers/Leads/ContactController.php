<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\Dialing;
use App\Model\Agentlog;
use Validator;
use App\Traits\CommonFunctionsTrait;
use App\Model\EmailProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Model\SmtpConfiguration;
use App\Mail\TestingBeforeklaviyo;
use Config;
use App\Traits\SendSmsToQueueTrait;
use App\Traits\VontageunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Model\FhinsureLog;
use App\Model\ContactStatus;

use View;

class ContactController extends Controller
{
	use CommonFunctionsTrait,SendSmsToQueueTrait,VontageunctionsTrait,KlaviyoFunctionsTrait;

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */

	function __construct()
	{

		$this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete|lead-file-list|lead-action', ['only' => ['index', 'contact_store',]]);
		$this->middleware('permission:lead-create', ['only' => ['create', 'contact_store']]);
		$this->middleware('permission:lead-edit', ['only' => ['edit', 'contact_update']]);
		// $this->middleware('permission:lead-delete', ['only' => ['contact_destroy', 'delete_contacts', 'remove_contacts']]);
		$this->middleware('permission:contact-delete', ['only' => ['contact_destroy', 'delete_contacts', 'remove_contacts']]);
	}
	public function index()
	{
		$cityCounts = Contact::select('c_city', DB::raw('COUNT(*) as total'))
	    ->whereNotNull('c_city')
	    ->where('c_city', '!=', '')
	    ->groupBy('c_city')
	    ->orderBy('c_city')
	    ->get();

		// echo "<pre>";print_r($cityCounts);exit;

		return view('contacts.index', compact('cityCounts'));
	}
	public function data(Request $request)
	{
	    if (!$request->ajax()) {
	        return response()->json(['message' => 'Invalid request'], 400);
	    }

	    $start = $request->input('start', 0);
	    $length = $request->input('length', 10);
	    $orderColumnIndex = $request->input('order')[0]['column'] ?? 0;
	    $orderColumnName = $request->input("columns.{$orderColumnIndex}.data", 'id');
	    $orderDirection = $request->input('order')[0]['dir'] ?? 'desc';
	    $searchValue = $request->input('search.value', '');

	    // Base query
	    $baseQuery = Contact::query();

	    // Apply filters
	    $baseQuery->when($request->filled('city'), function ($q) use ($request) {
	        $q->where('c_city', $request->city);
	    });

	    $baseQuery->when($request->filled('business_type'), function ($q) use ($request) {
	        $q->whereHas('leads', function ($subQuery) use ($request) {
	            $subQuery->where('type', $request->business_type);
	        });
	    });

	    $baseQuery->when($request->has('c_merge_status'), function ($q) use ($request) {
	        $q->whereHas('leads', function ($subQuery) use ($request) {
	            $subQuery->where('merge_status', $request->c_merge_status);
	        });
	    });

	    $filteredQuery = clone $baseQuery;

	    // Global Search
	    if (!empty($searchValue)) {
	        $searchableColumns = ['c_full_name', 'c_city', 'c_state', 'c_zip', 'c_phone', 'c_email', 'contact_slug'];
	        $searchQuery = $this->search($filteredQuery, $searchValue, $searchableColumns);

	        // $filteredQuery->where(function ($query) use ($searchValue, $searchableColumns) {
	        //     foreach ($searchableColumns as $column) {
	        //         $query->orWhere($column, 'like', "%{$searchValue}%");
	        //     }
	        // });
	    }

	    // $totalRecords = Contact::count();

	    // $filteredRecords = $filteredQuery->count();

	    // echo $filteredRecords;exit;

	    $data = $filteredQuery
	    	->select([
		        'id',
		        'c_full_name',
		        'c_city',
		        'c_state',
		        'c_zip',
		        'c_phone',
		        'c_email',
		        'c_is_client',
		        'contact_slug',
		        'c_merge_status',
		        'lead_id'
		    ])
		    ->with(['leads' => function ($query) {
		        $query->select(['id', 'type', 'merge_status']);
		    }])

	        // ->with('leads')
	        ->orderBy($orderColumnName, $orderDirection);
	        // ->offset($start)
	        // ->limit($length)
	        // ->get();

	    return Datatables::of($data)
	        ->addIndexColumn()
	        ->editColumn('c_is_client', function ($row) {
    			return $row->c_is_client ? 'yes' : 'no';
			})
	        ->addColumn('action', function ($row) {
	            $actions = '<div class="d-flex justify-content-center action-btns">';
	            if ($row->c_merge_status) {
	                $actions .= '<a href="/contacts/merge/' . $row->contact_slug . '" target="_blank" class="btn btn-sm btn-danger action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-compress"></i></a>';
	            }
	            $actions .= '<a href="/leads/show/' . base64_encode($row->lead_id) . '" title="View Contact Lead Record" class="btn btn-sm btn-info action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-eye"></i></a>';

	            if (auth()->user()->can('contact-delete')) {
	                $actions .= '<a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteModal(this, ' . $row->id . ')" class="btn btn-sm btn-danger action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-trash"></i></a>';
	            }
	            $actions .= '</div>';
	            return $actions;
	        })
	        ->rawColumns(['action'])
	        // ->setTotalRecords($totalRecords)
	        // ->setFilteredRecords($filteredRecords)
	        ->make(true);
	}


	public function data_old21042025(Request $request)
	{
		if ($request->ajax()) {
			$start = $request->input('start', 0);
			$length = $request->input('length', 10);
			$filter_on_column_number = $request->input('order')[0]['column'];
			$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
			$order_by = $request->input('order')[0]['dir'] ?? 'desc';
			$search_value = $request->input('search')['value'] ?? null;

			$searchQuery = Contact::with('leads');

			$totalRecords = $searchQuery->count();

			// Apply search filter
			if (!empty($search_value)) {
				$columns_to_search = ['c_full_name', 'c_city', 'c_state', 'c_zip', 'c_phone', 'c_email', 'contact_slug'];
				$searchQuery = $this->search($searchQuery, $search_value, $columns_to_search);
				$filteredRecords = $searchQuery->count();
			}
			else{
				$filteredRecords = $searchQuery->count();
			}

			if(!empty($request->input('city', null))){
				$searchQuery = $searchQuery->where('contacts.c_city',$request->input('city'));
			}
			if(!empty($request->input('business_type', null))){
				$searchQuery = $searchQuery->where('leads.type',$request->input('business_type'));
			}
			// value can be in 0/ 1
			if(!empty($request->input('c_merge_status', null))){ 
				$searchQuery = $searchQuery->where('leads.merge_status',$request->input('c_merge_status'));
			}

			$data = $searchQuery->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);

			return Datatables::of($data)
				->addIndexColumn()
				->editColumn('c_is_client', function ($row) {
					return $row->c_is_client ? 'yes' : 'no';
				})
				->addColumn('action', function ($row) {
					$actionBtn = '<div class="d-flex justify-content-center action-btns">';
					if ($row->c_merge_status) {
						$actionBtn .= '<a href="/contacts/merge/' . $row->contact_slug . '" target="_blank"  class="btn btn-sm btn-danger action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-compress"></i></a>';
					}
					$actionBtn .= '<a href="/leads/show/' . base64_encode($row->lead_id) . '" title="View Contact Lead Record" class="btn btn-sm btn-info action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-eye"></i></a>';

					if (auth()->user()->can('contact-delete')) {
						// $actionBtn .= '<a href="#" onclick="handleDelete(`' . route('leads.contact_destroy', $row->id) . '`)" class="btn btn-sm btn-danger action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-trash"></i></a>';
						$actionBtn .= '<a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteModal(this, ' . $row->id . ')" class="btn btn-sm btn-danger action-btn m-0 d-flex justify-content-center align-items-center"><i class="fa fa-trash"></i></a>';
					}
					$actionBtn .= '</div>';
					return $actionBtn;
				})
				->rawColumns(['action'])
				->setTotalRecords($totalRecords)
				->setFilteredRecords($filteredRecords)
				->make(true);
		}
	}

	public function updateContactStatus(Request $request)
	{
		$status = $request->status;
		$lead_id = (int) $request->lead_id;
		$contact_id = (int) $request->contact_id;
		$custom_status = 0;
		$message = 'Required fields are missing. Please contact your administrator!';
		if ($lead_id > 0 && $contact_id > 0 && !empty($status)) :
			$contact = Contact::find($contact_id);

			$removableStatus = ['Bad Number', 'Do Not Call', 'Not Interested', 'Call Back'];
			if (in_array($status, $removableStatus)) :
				$this->updateDialingLists($status, $contact_id, $lead_id);
			endif;
			$contact->update(['status' => $status, 'called_agent_id' => 0, 'agent_call_initiated' => 'no']);
			$message = 'Status updated successfully!';
			$custom_status = 1;
		endif;
		return json_encode([
			'status' => '200',
			'message' => $message,
			'custom_status' => $custom_status,
		]);
	}


	// update multiple contact's status
	public function contact_status_update(Request $request, $leadID)
	{
		$contact_status = $request->c_status;
		$lead = Lead::find($leadID);
		$allContactFromLeadId = Contact::where('lead_id', $leadID)->get();

		$contact_status = $request->c_status;
		try {
			// CONTACT table update first
			$c_agent_id = $request->c_agent_id;
			$agent_type_status = null;
			if(!empty($request->c_status)){
				$agent_type_status = ContactStatus::where('id',$request->c_status)->first();
				if($agent_type_status && $agent_type_status->status_type == 2){
					if(empty($c_agent_id)){
						toastr()->error("Selecting an agent is mandatory with ".$agent_type_status->name." status");
						return back()->withInput();
					}
					
				}
				// unset($agent_type_status);
			}
			$updateDone = Contact::where('lead_id', $leadID)->update(['c_status' => $contact_status,'c_agent_id'=>$c_agent_id]);

			if ($updateDone) {
				$own_status = $agent_type_status->display_in_pipedrive;
				// now updating 
				foreach ($allContactFromLeadId as $singleContact) {
					// $workableStatus = [
					// 	'Bad Number', 'Do Not Call', 'Not Interested', 'Call Back',
					// 	'No Answer (Left Message)', 'Policies Received', 'AOR Received', 'Select Status'
					// ];

					// if (in_array($contact_status, $workableStatus)) :
					if($agent_type_status && empty($agent_type_status->false_status)){
						$this->updateDialingLists($contact_status, $singleContact->id, $leadID,$c_agent_id,$own_status);
						$this->setContactToQueue($lead);

						$message = auth()->user()->name . ' has updated status of contact : ' . $singleContact->id . ' to ' . $contact_status . ' present in lead: ' . $leadID;
						Agentlog::updateOrCreate(
							['user_id' => auth()->user()->id, 'contact_id' => $singleContact->id],
							['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $leadID, 'contact_id' => $singleContact->id, 'status' => 'call_status_updated']
						);
					}
						
					// endif;
				}

				$this->contactbasedleadstatusupdate($leadID,$c_agent_id,$contact_status);


				
				toastr()->success("Updated successfully");
				return back();
			} else {
				toastr()->error("No contacts were updated.");
				return back();
			}
		} catch (\Exception $e) {
			toastr()->error($e->getMessage());
			return back();
		}
	}

	public function merge(Request $request, $slug)
	{
		$contacts = Contact::where('contact_slug', $slug)->get();
		if ($contacts->count() <= 0) :
			toastr()->error("No mergeable contacts exists for above slug.");
			return back();
		endif;
		$compareArr = array();

		foreach ($contacts as $key => $contact) :
			$columns = $contact->getFillable();
			// dd($columns);
			$contactData = [];
			$attributes = array_diff_key($contact->getAttributes(), array_flip(['c_is_client', 'c_merge_status', 'agent_call_initiated',  'deleted_at', 'has_initiated_stop_chat', 'called_agent_id']));
			foreach ($attributes as $key => $value) {

				$contactData[$key] = $value;
			}
			$compareArr[] = $contactData;
		endforeach;
		return view('contacts.merge_contacts', compact('compareArr'));
	}

	public function completemerge(Request $request)
	{
		$payloadData = $request->all();
		// dd($payloadData);
		try {
			// Find leads with the specified lead_slug, excluding the lead with the specified id
			Contact::where('contact_slug', $payloadData['contact_slug'])
				->where('id', '!=', $payloadData['id'])
				->delete();

			// Update the lead with the specified id
			$payloadData['c_merge_status'] = 0;
			Contact::where('id', $payloadData['id'])
				->update($payloadData);

			// Return success response
			return response()->json(['status' => true, 'message' => 'Contact merged successfully!']);
		} catch (\Exception $e) {
			// Return error response
			return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
		}
	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function contact_update(Request $request, $id)
	{
		$rules = [
			'c_first_name' => 'required|string|max:191',
			'c_last_name' => 'required|string|max:191',
			'c_title' => 'nullable|string|max:191',
			'c_address1' => 'required|string|max:191|regex:/^\d.*/',
			'c_address2' => 'nullable|string|max:191',
			'c_city' => 'nullable|string|max:191',
			'c_state' => 'nullable|string|max:191',
			'c_county' => 'nullable|string|max:191',
			'c_zip' => 'nullable|max:5|string',
			'c_phone' => 'nullable',
			'c_email' => 'nullable|email|max:191',
		];
		$niceNames = [
			'c_first_name' => 'First Name',
			'c_last_name' => 'Last Name',
			'c_address1' => 'Address 1',
			'c_address2' => 'Adress 2',
			'c_city' => 'City',
			'c_state' => 'State',
			'c_zip' => 'Zip',
			'c_county' => 'County',
			'c_phone' => 'Phone',
			'c_email' => 'Email',
		];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput(array_merge($request->all(), [
		        'contact_id' => $id
		    ]));
    
		}
		$c_agent_id = $request->c_agent_id;
		// $all_account_list_permission = auth()->user()->can('all-accounts-list-pipedrive');
		// if($all_account_list_permission && (empty($request->c_status) || $request->c_status == 1)){
		// 	if(!empty($request->c_agent_id)){
		// 		toastr()->error("You cannot select an agent without selecting a status");
		// 		return back()->withInput();
		// 	}
		// }
		// if(!$all_account_list_permission){
		// 	$c_agent_id = auth()->user()->id;
		// }
		// else{
		// 	if(!empty($request->c_agent_id)){
		// 		$c_agent_id = $request->c_agent_id;
		// 	}
		// }
		if(!empty($request->c_status)){
			$agent_type_status = ContactStatus::where('id',$request->c_status)->first();
			if($agent_type_status && $agent_type_status->status_type == 2){
				if(empty($c_agent_id)){
					toastr()->error("Selecting an agent is mandatory with ".$agent_type_status->name." status");
					return back()->withInput(array_merge($request->all(), [
				        'contact_id' => $id
				    ]));
				}
				
			}
			// unset($agent_type_status);
		}
		
		$addressWithNumber = $request->c_address1;
		if (preg_match('/\d+/', $request->c_address1, $matches)) {
			$addressWithNumber = $matches[0];
		}
		$contactSlug = $this->generateSlug([$request->c_first_name, $request->c_last_name, $addressWithNumber]);
		// dd($contactSlug);

		$contactExistance = $this->checkContactSlugExistance($contactSlug, $id);
		if (is_array($contactExistance) && isset($contactExistance["existanceCount"]) && $contactExistance['existanceCount'] > 0) {
			toastr()->error(implode('</br>', $contactExistance['message']));
			return back()->withErrors($validator)->withInput(array_merge($request->all(), [
		        'contact_id' => $id
		    ]));
		}

		$input = $request->all();
		$input['c_agent_id'] = $c_agent_id; 

		//get the contact to update
		$contact = Contact::find($id);
		$oldcontact = clone $contact;
		if (!$contact) {
			toastr()->error('Something went wrong');
			return back();
		}

		$contact->update($input);
		if (!$request->c_is_client) {
			$contact->update(['c_is_client' => 0]);
		}
		//add fullname and slug
		$contact->update([
			'c_full_name' => $request->c_first_name . ' ' . $request->c_last_name,
			'contact_slug' => $contactSlug
		]);	

		// commenting this line for now as no need to send sms and klaviyo for now
		// $this->updatecontactinfomation_basedklaviyovontage($oldcontact,$contact);

		$lead = Lead::find($contact->leads->id);

		// add contact client status as per lead
		if (($lead->is_client == '1')) {
			$contact->update([
				'c_is_client' => 1
			]);
		} else {
			$contact->update([
				'c_is_client' => 0
			]);
		}
		// echo "<pre>";print_r($contact);exit;
		$this->contactbasedleadstatusupdate($contact->leads->id,$c_agent_id,$request->c_status);

		if (!empty($request->c_status) && $agent_type_status) {

			// $contact->update(['status' => $request->c_status]);
			// $workableStatus = [
			// 	'Bad Number', 'Do Not Call', 'Not Interested', 'Call Back',
			// 	'No Answer (Left Message)', 'Policies Received', 'AOR Received', 'Select Status'
			// ];
			// if (in_array($request->c_status, $workableStatus)) :
			// if(!empty($contact->c_phone)){
				$own_status = $agent_type_status->display_in_pipedrive;
				$this->updateDialingLists($request->c_status, $contact->id, $contact->leads->id,$c_agent_id,$own_status);
				$this->setContactToQueue($lead);

				$message = auth()->user()->name . ' has updated status of contact : ' . $contact->id . ' to ' . $request->c_status . ' present in lead: ' . $contact->leads->id;
				Agentlog::updateOrCreate(
					['user_id' => auth()->user()->id, 'contact_id' => $contact->id],
					['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $contact->leads->id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
				);
			// }
			// endif;
		}
		create_log($lead, 'Edit Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');
		toastr()->success('Contact <b>' . $contact->first_name . ' ' . $contact->last_name . '</b> updated successfully');
		session(['remove_sessionstorage' => 1]);
		return redirect()->back();
	}

	public function updateDialingLists_old($status_id, $contact_id, $lead_id,$c_agent_id=0,$own_status=0)
	{
		$lead_dialing_ids = DB::table('dialings_leads')->where('lead_id', $lead_id)->where('assigned_to_agent_id', $c_agent_id)->get()->pluck('dialing_id')->toArray();
		if(empty($lead_dialing_ids)){
			$fetch_referal_dailing_id = $this->fetchedReferalDialingId();
			$this->assigAgentToDialing($c_agent_id,$fetch_referal_dailing_id,1);
			$this->addLeadWithDialing($c_agent_id,$fetch_referal_dailing_id,$lead_id);

			$lead_dialing_ids = [$fetch_referal_dailing_id];
		}

		if(empty($c_agent_id)){
			$c_agent_id = auth()->user()->id;
		}

		$ownStatusArray = ContactStatus::where('status_type', 2)->pluck('id')->toArray();

		$ownContactStatus = Contact::where('lead_id', $lead_id)
		    ->whereNotNull('c_phone')
		    ->whereIn('c_status', $ownStatusArray)
		    ->exists();


		$status = 'free';
		$updateArr = ['owned_by_agent_id' => 0, 'status' => 'free','ownmarked_at'=>null];
		// dd($ownContactStatus);
		if ($ownContactStatus) {
			$estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
			$est_timenow = $estTime->format('Y-m-d H:i:s');
			$updateArr = ['owned_by_agent_id' => auth()->user()->id, 'status' => 'own','ownmarked_at'=> $est_timenow ];
		}



		foreach ($lead_dialing_ids as $dialing_id) :
			// print_r($updateArr);
			// die();
			// echo $dialing_id."-----------".$lead_id."-----------".$c_agent_id;
			// echo "<pre>";print_r($updateArr);exit;

			DB::table('dialings_leads')->where('dialing_id', $dialing_id)
				->where('lead_id', $lead_id)
				->where('assigned_to_agent_id', $c_agent_id)
				->update($updateArr);

			// echo "<pre>";
			// print_r($ownStatusArray);print_r($updateArr);
			// echo $dialing_id."     ".$lead_id."     ".$c_agent_id;
		endforeach;
		// exit;

	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function contact_store(Request $request, $leadID)
	{
		// $userId = isset($user) ? $user->id : null;
		// echo "<pre>";print_r($request->input());exit;
		$rules = [
			'c_first_name' => 'required|string|max:191',
			'c_last_name' => 'required|string|max:191',
			'c_title' => 'nullable|string|max:191',
			'c_address1' => 'required|string|max:191|regex:/^\d.*/',
			'c_address2' => 'nullable|string|max:191',
			'c_city' => 'nullable|string|max:191',
			'c_state' => 'nullable|string|max:191',
			'c_county' => 'nullable|string|max:191',
			'c_zip' => 'nullable|max:5|string',
			'c_phone' => 'nullable',
			'c_email' => 'nullable|email|max:191',

		];
		$niceNames = [
			'c_first_name' => 'First Name',
			'c_last_name' => 'Last Name',
			'c_address1' => 'Address 1',
			'c_address2' => 'Adress 2',
			'c_city' => 'City',
			'c_state' => 'State',
			'c_zip' => 'Zip',
			'c_county' => 'County',
			'c_phone' => 'Phone',
			'c_email' => 'Email',
		];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}
		$c_agent_id = $request->c_agent_id;
		// $all_account_list_permission = auth()->user()->can('all-accounts-list-pipedrive');
		// if(!$all_account_list_permission){
		// 	$c_agent_id = auth()->user()->id;
		// }
		// else{
		// 	if(!empty($request->c_agent_id)){
		// 		$c_agent_id = $request->c_agent_id;
		// 	}
		// }
		if(!empty($request->c_status)){
			$agent_type_status = ContactStatus::where('id',$request->c_status)->first();
			if($agent_type_status && $agent_type_status->status_type == 2){
				if(empty($c_agent_id)){
					toastr()->error("Selecting an agent is mandatory with ".$agent_type_status->name." status");
					return back()->withInput();
				}
				
			}
			// unset($agent_type_status);
		}
		// echo $c_agent_id;exit;
		// if(!empty($request->c_status)){
		// 	$agent_type_status = ContactStatus::where('id',$request->c_status)
		// 	// ->where('display_in_pipedrive',1)
		// 	->first();
		// 	if($agent_type_status){
		// 		if(!$is_admin){
		// 			$c_agent_id = auth()->user()->id;
		// 		}
		// 		else{
		// 			if(!empty($request->c_agent_id)){
		// 				$c_agent_id = $request->c_agent_id;
		// 			}
		// 			else{
		// 				toastr()->error("Selecting an agent is mandatory with ".$agent_type_status->name." status");
		// 				return back()->withInput();
		// 			}
		// 		}
				
		// 	}
		// 	// unset($agent_type_status);
		// }
		$addressWithNumber = $request->c_address1;
		if (preg_match('/\d+/', $request->c_address1, $matches)) {
			$addressWithNumber = $matches[0];
		}
		$contactSlug = $this->generateSlug([$request->c_first_name, $request->c_last_name, $addressWithNumber]);

		$contactExistance = $this->checkContactSlugExistance($contactSlug);
		if (is_array($contactExistance) && isset($contactExistance["existanceCount"]) && $contactExistance['existanceCount'] > 0) {
			toastr()->error(implode('</br>', $contactExistance['message']));
			return back()->withErrors($validator)->withInput();
		}
		$input = $request->all();
		$input['c_agent_id'] = $c_agent_id;

		//create new contact
		$contact = Contact::create($input);
		//add fullname
		$contact->c_full_name = $request->c_first_name . ' ' . $request->c_last_name;
		$contact->contact_slug = $contactSlug;
		//get the lead where the contact was added
		$lead = Lead::find($leadID);
		//attach the contact to lead
		$contact->leads()->associate($lead); //update the model

		// add contact client status as per lead
		$contact->c_is_client = ($lead->is_client == '1') ? 1 : 0;

		$contact->save();
		// commenting this line for now as no need to send sms and klaviyo for now
		// $this->sendcontactwisesmsproviderandklaviyo($contact);

		$this->contactbasedleadstatusupdate($leadID,$c_agent_id,$request->c_status);

		if (!empty($request->c_status) && $agent_type_status) {

			// $contact->update(['status' => $request->c_status]);
			// $workableStatus = [
			// 	'Bad Number', 'Do Not Call', 'Not Interested', 'Call Back',
			// 	'No Answer (Left Message)', 'Policies Received', 'AOR Received', 'Select Status'
			// ];
			// if (in_array($request->c_status, $workableStatus)) :
			if(empty($agent_type_status->false_status)){
				$own_status = $agent_type_status->display_in_pipedrive;
				$this->updateDialingLists($request->c_status, $contact->id, $leadID,$c_agent_id,$own_status);
				$this->setContactToQueue($lead);

				$message = auth()->user()->name . ' has updated status of contact : ' . $contact->id . ' to ' . $request->c_status . ' present in lead: ' . $contact->leads->id;
				Agentlog::updateOrCreate(
					['user_id' => auth()->user()->id, 'contact_id' => $contact->id],
					['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $contact->leads->id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
				);
			}
			// endif;
		}
		
		create_log($lead, 'Create Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');
		toastr()->success('Contact <b>' . $contact->first_name . ' ' . $contact->last_name . '</b> created successfully');
		return redirect()->back();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function contact_destroy(Request $request, $id)
	{
		//get the contact to delete
		$contact = Contact::find($id);
		if (!$contact) {

			toastr()->error('The Contact was removed previously');
			return back();
		}
		// $lead = Lead::find($contact->leads->id);
		$name = $contact->c_first_name . ' ' . $contact->c_last_name;
		// create_log($lead, 'Delete Contact : ' . $name, '');

		if (is_object($contact) && isset($contact->leads->id)) {
			$lead = Lead::find($contact->leads->id);
			create_log($lead, 'Delete Contact : ' . $name, '');
		}
		$contact->delete();

		if ($request->ajax()) {
			return response()->json(['contactsCount' => 1, 'message' => 'Contact <b>' . $name . '</b> Deleted successfully!']);
		}
		toastr()->success('Contact <b>' . $name . '</b> Deleted!');
		return redirect()->back();
	}


	/**
	 * Display remove bulk contacts page
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function remove_contacts()
	{

		return view('contacts.remove_contacts');
	}

	/**
	 * Deletes bulk contacts
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function delete_contacts(Request $request)
	{
		$contactIds = $request->selectedValues;
		if (count($contactIds) <= 0) :
			return response()->json(['contactsCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;

		// Retrieve leads based on the array of IDs
		$contacts = Contact::whereIn('id', $contactIds)->get();
		// Loop through the contacts
		foreach ($contacts as $contact) {
			// Delete all contacts related to the lead


			//create Lead Log
			$leadlog = new Log();
			$leadlog->action = 'Remove Contact : ' . $contact->c_full_name;
			$leadlog->users()->associate(auth()->user())->save(); //associate user

			//remove contact
			$contact->delete();
		}
		return response()->json(['contactsCount' => 1, 'message' => 'Records deleted successfully']);
	}

	public function mark_comolete_chat(Request $request)
	{
		$contacts = Contact::where('id', $request->contact_id)->first();
		if($contacts){
			$contacts->agent_marked_conversation_ended = 1;
			$contacts->save();
		}
		return response()->json(['success' => true, 'message' => 'Marked conversation as completed']);
	}

	public function mark_stop_chat(Request $request)
	{
		$contacts = Contact::where('id', $request->contact_id)->first();
		if($contacts){
			$contacts->has_initiated_stop_chat = 1;
			$contacts->save();
		}
		return response()->json(['success' => true, 'message' => 'stop further conversation on this contact']);
	}
	
	/**
	 * Read data from csv file
	 * @param object $csvFile
	 * @return array $csvData
	 */
	private static function readDataFromCsv($csvFile, $extension)
	{

		//store file
		$fileName = Carbon::now()->format('mdYHisu');
		//if the file is xlsx or xls , convert it to csv
		if ($extension == "xlsx" || $extension == "xls") {
			if ($extension == "xlsx") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			} else if ($extension == "xls") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
			}

			$reader->setReadDataOnly(true);


			$path = '../storage/app/public/uploads/' . $fileName . '.csv';
			$excel = $reader->load($csvFile);
			// dd($excel);
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
			// $writer->setUseBOM(true);
			// $writer->setOutputEncoding('UTF-8');
			$writer->setUseBOM(false);
			$writer->setOutputEncoding('UTF-8');
			$writer->setEnclosureRequired(false);
			$writer->save($path);

			$csvFile =  $path;
		} else {

			$file = Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
		}

		$delimiter = ',';
		$header = null;
		$csvData = array();
		//the required columns
		$requiredColumns = array(
			1 => "Contact_First_Name",
			2 => "Contact_Last_Name",
			3 => "Contact_Address1"
		);
		//read data and add it to array
		if (($handle = fopen($csvFile, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

				if (!$header) {
					$header = $row;
					//loop trough required columns and if one of them is missing in csv, send error
					foreach ($requiredColumns as $req) {

						if (!in_array($req, $header)) {

							$ColumnError =  'Column ' . $req . ' is missing. File was not parsed.';
							return array('errors' => $ColumnError);
						}
					}
				} else {

					if (count($header) > count($row)) {

						$csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), "")), 'UTF-8', 'UTF-8');
					} else if (count($header) < count($row)) {
						$csvData[] =  mb_convert_encoding(array_combine($header, array_slice($rows, 0, count($header))), 'UTF-8', 'UTF-8');
					} else {
						$csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
					}
				}
			}
			fclose($handle);
		}
		return $csvData;
	}

	public function sendContactMail(Request $request)
	{
		$rules = [
			'template_subject'  => 'required',
			'template_content' => 'required'
		];

		$niceNames = [
			'template_subject' => 'Subject',
			'template_content' => 'Content',
		];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			return response()->json([
				'status' => 422,
				'errors' => $validator->errors()->all()
			]);
		}
		try {
			$this->setDynamicSMTP();
			$validatedData = $validator->validated();
			$input = $request->all();
			$from_user = auth()->user()->id;

			$data['subject'] = $input['template_subject'];
			$data['content'] = $input['template_content'];
			$smtp_data = SmtpConfiguration::where('user_id', auth()->user()->id)->first();
			$data['signature_image'] = $smtp_data['signature_image'];
			$data['signature_text'] = $smtp_data['signature_text'];

			if(str_contains($input['current_path'], 'newsletter')) {

				$contactDetail = FhinsureLog::where('id', $input['contact_id'])->first();
				$data['content'] = str_replace("{CANDIDATE_FIRST_NAME}", $contactDetail->first_name, $data['content']);
				$data['content'] = str_replace("{CANDIDATE_LAST_NAME}", $contactDetail->last_name, $data['content']);
				$to_address = $contactDetail->email;
				$data['module_name'] = "newsletter";
				$data['newsletter_id'] = $input['contact_id'];

			} else {

				$contactDetail = Contact::where('id', $input['contact_id'])->with('leads:id,name')->first();
				$data['content'] = str_replace("{CANDIDATE_FIRST_NAME}", $contactDetail->c_first_name, $data['content']);
				$data['content'] = str_replace("{CANDIDATE_LAST_NAME}", $contactDetail->c_last_name, $data['content']);
				$data['content'] = str_replace("{BUSINESS_NAME}", $contactDetail->leads->name, $data['content']);
				$to_address = $contactDetail->c_email;
				$data['module_name'] = "contact";
				$data['contact_id'] = $input['contact_id'];
				
			}	

			// Mail::to("suparna.dey@codeclouds.in")->send(new ContactMail($data));
			Mail::to($to_address)->send(new ContactMail($data));

			$this->saveEmailData($data);

			return response()->json([
				'status' => 200,
				'response' => Config::get('mail'),
				'message' => 'Email sent successfully'
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => 500,
				'response' => "Failed to send the email. Please contact the administrator.",
				'error' =>  $e->getMessage()
			]);
		}
	}

	public function testurl()
	{
		if(auth()->user()){
			$credentials = auth()->user();

			return response()->json([
				'status' => 200,
				'message' => 'auth found',
				'isLoggedIn' => true,
				"credentials" =>$credentials
			]);
		}
		else{
			return response()->json([
				'status' => 200,
				'message' => 'auth not found',
				'isLoggedIn' => false,
				"credentials" => null
			]);
		}
		exit;


		$data['content'] = '<div>
                <p>Hi Rohit,</p><p>We spoke at the trade show regarding your condo insurance.</p><p>Let me know when a good time to call will be.</p><p>Sincerely,</p>
            </div>';


        $data['signature_text'] =  '<p>Bisakha Pati</p><p>Agent,Generic Tech Solutions</p><p>Office: <a href="tel:(555) 123-4567">(555) 015-2720</a> Cell: <a href="tel:(555) 010-2020">(555) 123-4567</a></p><p><a href="mailto:Nsledge@fhinsure.com">johndoe@example.com</a></p><p><a href="https://datastarpro.com/smtps/www.fhinsure.com">www.example.com</a></p><p>123 Main Street, Anytown, Anystate, 12345</p>';

        $data['signature_image'] = '1719572603.jpg';


		return view('emails.contact-email', compact('data'));
	}


	public function checkTestingMailConfiguration()
    {
        $whereCond = [
            ['username', '!=', ''],
            ['password', '!=', ''],
            ['host', '!=', ''],
            ['port', '!=', ''],
            ['encryption', '!=', ''],
            ['from_name', '!=', ''],

        ];
        $smtp_count = SmtpConfiguration::where('user_id', 20)
            ->where($whereCond)
            ->count();
        return $smtp_count;
    }
}
