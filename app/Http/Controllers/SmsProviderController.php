<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\SmsProvider;
use App\Model\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use DataTables;
use Redirect, Response;
use App\Model\LeadsModel\Contact;
use Carbon\Carbon;
use App\Jobs\SendSmsContactUpdate;


class SmsProviderController extends Controller
{
	public function index()
	{
		$is_admin = auth()->user()->can('agent-create');
		if($is_admin){
			$smsproviders = [];

			return view('smsprovider.index', compact('smsproviders'));
		}
		return redirect('/unauthorised');
	}

	public function unauthorised()
	{
		return view('smsprovider.unauthorised');
	}

	public function listIndex($type=0,$id=0)
	{
		$can_access = false;
		$is_admin = auth()->user()->can('agent-create');
		if($is_admin){
			$can_access = true;
		}
		else{
			if(auth()->user()->id == 26){
				$can_access = true;
			}
		}
		if($can_access){
			return view('smsprovider.listIndex',compact('type','id'));
		}
		return redirect('/unauthorised');
	}

	public function data(Request $request)
	{
        $start = $request->input('start', 0);
		$length = $request->input('length', 10); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';

        $smsproviders = SmsProvider::orderBy($filter_on_column_name, $order_by)
        ->offset($start)
        ->limit($length)
        ->get();

        return datatables()->of( $smsproviders)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
			return view('smsprovider.partials.buttons-actions', compact('row'));			
		})
		->rawColumns(['action'])
		// ->setTotalRecords($totalRecords)
		->make(true);
	}

	public function listdata(Request $request)
	{
        $messagelist = Message::query();

        $user_type_selection = $request->user_type_selection;

		$messagelist = $messagelist->when(true, function ($query) use ($user_type_selection) {
		    // Select required fields and add SQL aliases
		    $query->select('contact_id','newsletter_id','through_sms_provider_flag','has_initiated_stop_chat')
		    ->addSelect([
		        DB::raw("CONCAT(COALESCE(contacts.c_first_name, newsletters.first_name), ' ', COALESCE(contacts.c_last_name, newsletters.last_name)) as full_name"),
		        DB::raw('COALESCE(contacts.c_phone, newsletters.phone) as c_phone'),
		        DB::raw("SUM(CASE WHEN messages.chat_type = 'outbound' THEN 1 ELSE 0 END) as outbound_count"),
		        DB::raw("SUM(CASE WHEN messages.chat_type = 'inbound' THEN 1 ELSE 0 END) as inbound_count"),
		        DB::raw('contacts.lead_id'),
		        // Add last outbound time
            	DB::raw("MAX(CASE WHEN messages.chat_type = 'outbound' THEN messages.created_at ELSE NULL END) as last_out_time"),
            	// Add last inbound time
            	DB::raw("MAX(CASE WHEN messages.chat_type = 'inbound' THEN messages.created_at ELSE NULL END) as last_in_time")
		    ]);

		    // Left join with contacts and newsletters tables based on the flag
		    $query->leftJoin('contacts', function ($join) {
		        $join->on('messages.contact_id', '=', 'contacts.id')
		            ->where('messages.through_sms_provider_flag', 1);
		            // ->where('contacts.has_initiated_stop_chat', 0);
		    });

		    $query->leftJoin('newsletters', function ($join) {
		        $join->on('messages.newsletter_id', '=', 'newsletters.id')
		            ->where('messages.through_sms_provider_flag', 2);
		    });

		    $query->where(function ($query) use ($user_type_selection) {
			    $query->whereNull('contacts.id') // Include when there's no matching contact (for newsletters)
		        ->orWhere(function ($query1) use ($user_type_selection) {
		            if ($user_type_selection == 1) {
		                $query1->where('contacts.has_initiated_stop_chat', 0) // Only include contacts where 'has_initiated_stop_chat' is 0
	                    ->whereNull('contacts.agent_marked_conversation_ended') // Both conditions must be true
	                    ->whereNull('contacts.archive_sms'); // Both conditions must be true
		            } elseif ($user_type_selection == 2) {
		                $query1->whereNotNull('contacts.agent_marked_conversation_ended');
		            } elseif ($user_type_selection == 3) {
		                $query1->where('contacts.has_initiated_stop_chat', 1);
		            } elseif ($user_type_selection == 4) {
		                $query1->whereNotNull('contacts.archive_sms')
	                    ->whereNull('contacts.agent_marked_conversation_ended')
	                    ->where('contacts.has_initiated_stop_chat', 0);
		            }
		        });
			});

		    // Group by phone number
		    $query->groupBy('contact_id')->groupBy('newsletter_id');

		    return $query;
		});

		if (!empty($request->through_sms_provider_flag)) {
		    $messagelist = $messagelist->where('through_sms_provider_flag',$request->through_sms_provider_flag);
		}
		else{
			$messagelist = $messagelist->whereNotNull('through_sms_provider_flag');;
		}

		if (!empty($request->outbound_type_value)) {
		    $messagelist = $messagelist->having('outbound_count',$request->outbound_type_selection ,$request->outbound_type_value);
		}
		if (!empty($request->inbound_type_value)) {
		    $messagelist = $messagelist->having('inbound_count',$request->inbound_type_selection ,$request->inbound_type_value);
		}

		// $messagelist = datatables()->of($messagelist)->toArray();
		// dd($messagelist);

		return datatables()->of($messagelist)
		->addColumn('actions', function ($messages) {
			return '<div class="d-flex justify-content-center action-btns">
			<a href="javascript:void(0)" title="View Chat" class="view_chat_button chat_initialise newsletters_chat_'.$messages->newsletter_id.' contact_chat_'.$messages->contact_id.'"
            data-name="'.$messages->full_name.'"
            data-chat_contact_status="'.$messages->has_initiated_stop_chat.'" 
			data-contact_id="'.$messages->contact_id.'" 
			data-newsletter_id="'.$messages->newsletter_id.'" 
			data-through_sms_provider_flag="'.$messages->through_sms_provider_flag.'">
			<i class="fa fa-comments"></i></a></div>';
		})
		->addColumn('mark_complete', function ($messages) use ($user_type_selection){
			if($messages->contact_id){
				$return_string =  '<div class="d-flex justify-content-center action-btns"><a href="javascript:void(0)" title="Mark Complete" class="mark_comolete_button';

				if($user_type_selection != 1){
					$return_string .= ' notClickAble';
				}

				$return_string .= ' mark_comolete_button_chat'.$messages->contact_id.'" data-contact_id="'.$messages->contact_id.'"><i class="fa fa-check"></i></a></div>';
			}
			else{
				$return_string = '-';
			}
			return $return_string;
			
		})
		->addColumn('mark_stop', function ($messages) use ($user_type_selection){
			if($messages->contact_id){
				$return_string = '<div class="d-flex justify-content-center action-btns"><a href="javascript:void(0)" title="Mark Stop" class="mark_stop_button';

				if($user_type_selection != 1){
					$return_string .= ' notClickAble';
				}

				$return_string .= ' mark_stop_button_chat'.$messages->contact_id.'" data-contact_id="'.$messages->contact_id.'"><i class="fa fa-ban"></i></a></div>';
			}
			else{
				$return_string = '-';
			}
			return $return_string;
			
		})
		->addColumn('name_area', function ($messages) {
			if($messages->through_sms_provider_flag == 2){
				return '<p>'.$messages->full_name.'</p>';
			}
			else{
				return '<a href="javascript:void(0)" class="name_area" data-lead_id="'.$messages->lead_id.'" data-contact_id="'.$messages->contact_id.'" data-newsletter_id="'.$messages->newsletter_id.'" data-through_sms_provider_flag="'.$messages->through_sms_provider_flag.'">'.$messages->full_name.'</a>';
			}
			
		})
		->rawColumns(['actions','name_area','mark_complete','mark_stop'])
		->make(true);

	}

    public function create()
	{		
		// return view('smsprovider.create', compact('is_admin','agent_users'));
		$is_admin = auth()->user()->can('agent-create');
		if($is_admin){
			return view('smsprovider.create');
		}
		return redirect('/unauthorised');
	}

    public function store(Request $request)
	{
		$rules =[
			'cycle_name' => 'required|max:100',
			'minute_delay' => 'required|max:100',
			'day_delay' => 'required|max:100',
			'text' => 'required|max:200'
		];

		$niceNames = [
			'cycle_name' => 'Cycle Id',
			'minute_delay' => 'Minute Delay',
			'day_delay' => 'Day Delay',
			'text' => 'Text',
		];
		// echo "<pre>";
		// print_r($request->input());exit;
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();

		// $input['text'] = strip_tags(trim($input['text']));
		$smsprovider = SmsProvider::create($input);
		$id = $smsprovider->id;

		// Logic for smsprovider queue & contact table
		if($smsprovider){
			$this->contactUpdateAsPerAddUpdate($smsprovider);
		}

		// toastr()->success('Template <b>' . $smsprovider->smsprovider_name . '</b> created successfully');
		toastr()->success('Sms Provider created successfully');
		// return redirect()->route('smsprovider.update', compact('id'));
		return redirect()->route('smsprovider.index');
	}


    public function show(Request $request, $id)
	{
        // dd($request);
        $id = base64_decode($id);
		$smsprovider = SmsProvider::findOrFail($id);
		if (!$smsprovider) {

			toastr()->error('This SmsProvider doesn\'t exist');
			return redirect('/smsprovider');
		}
		// $smsprovider = SmsProvider::where('id', $id)->with('user')->first();
		return view('smsprovider.show', compact('smsprovider'));
	}

    public function edit($id)
	{	
		$is_admin = auth()->user()->can('agent-create');
		if($is_admin){
			$id = base64_decode($id);
			$smsprovider = SmsProvider::find($id);
			if (!$smsprovider) {

				toastr()->error('This SmsProvider doesn\'t exist');
				return redirect('/smsprovider');
			}
			return view('smsprovider.edit', compact('smsprovider'));
		}
		return redirect('/unauthorised');

	}

    public function update(Request $request, $id)
	{
		
		$smsprovider = SmsProvider::find($id);
		if (!$smsprovider) {
			toastr()->error('Something went wrong');
			return back();
		}

		$rules =[
			'cycle_name' => 'required|max:100',
			'minute_delay' => 'required|max:100',
			'day_delay' => 'required|max:100',
			'text' => 'required|max:200'
		];

		$niceNames = [
			'cycle_name' => 'Cycle Id',
			'minute_delay' => 'Minute Delay',
			'day_delay' => 'Day Delay',
			'text' => 'Text',
		];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();
		// echo "<pre>";
		// print_r($input);exit;
		// $input['text'] = strip_tags(trim($input['text']));
		$smsprovider->update($input);

		// Logic for smsprovider queue & contact table
		if($smsprovider){
			$this->contactUpdateAsPerAddUpdate($smsprovider, 'delay_update');
		}

		// toastr()->success('Sms Provider ' . $input['smsprovider_name'] . ' updated successfully');
		toastr()->success('Sms Provider updated successfully');
		return redirect()->back();
		
	}

    public function delete_smsprovider(Request $request)
	{
		$smsproviderIds = $request->selectedValues;
		if (count($smsproviderIds) <= 0) :
			return response()->json(['smsproviderCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;

		SmsProvider::whereIn('id', $smsproviderIds)->delete();

		return response()->json(['smsproviderCount' => 1, 'message' => 'Records deleted successfully']);
	}

    public function destroy($id)
	{
		//find the smsprovider to delete
		$smsprovider = SmsProvider::find($id);
		if (!$smsprovider) {

			toastr()->error('The SmsProvider was removed previously');
			return back();
		}
		$smsprovider->delete();
		// toastr()->success('SmsProvider <b>' . $smsprovider->smsprovider_name . '</b> Deleted!');
		toastr()->success('SmsProvider Deleted!');
		return  redirect()->route('smsprovider.index');
	}

	public function contactUpdateAsPerAddUpdate($smsprovider, $updateFlag = ''){

		SendSmsContactUpdate::dispatch($smsprovider, $updateFlag);
		
	}
}
