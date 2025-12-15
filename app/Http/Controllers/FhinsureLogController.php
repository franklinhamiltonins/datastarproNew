<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use Redirect, Response;
use DataTables;
use App\Model\FhinsureLog;
use App\Traits\CommonFunctionsTrait;
use  App\Model\Message;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FhinsureLogController extends Controller
{ 
    use CommonFunctionsTrait;

    function __construct()
	{
		$this->middleware('permission:agent-create', ['only' => ['index', 'get_fhinsure_log', 'show', 'destroy', 'delete_logs']]);
	}

    /**
	 * Create the specified resource.
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
        $request = $request->all();
        try {
            
			$input = [
                'first_name' => $request['first_name'],
                'last_name'  => $request['last_name'],
                'email'      => $request['email'],
                'phone'      => $request['phone'] ?? "",
                'zip'        => $request['zip'] ?? "",
                'insurance_type' => $request['insurance_type'] ?? "",
                'is_checked' => $request['is_checked'] ?? 'no',
                'site_name'  => $request['site_name'] ?? "",
                'profile_add_status' 	=> $request['profile_add_status'] ?? "",
                'profile_add_response' 	=> $request['profile_add_response'] ?? "",
                'list_add_status' 		=> $request['list_add_status'] ?? "",
                'list_add_response' 	=> $request['list_add_response'] ?? ""
            ];
            FhinsureLog::create($input);
			// Return success response
			return response()->json(['status' => true, 'message' => 'Data Submitted Successfully..', 'data' => json_encode($input)]);
		} catch (\Exception $e) {
			// Return error response
			return response()->json(['status' => 'error', 'error' => $e->getMessage(), 'message' => 'Failed! Please try again later..', 'data' => json_encode($request)], 500);
		}
    }

    public function index($id=0)
	{
		$smtp_data = $this->checkMailConfiguration();
		return view('fhinsure_logs.index', compact('smtp_data','id'));
	}

	public function get_fhinsure_log(Request $request)
	{
		$start = $request->input('start', 0);
		$length = $request->input('length', 5); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;

		$searchQuery = FhinsureLog::query();

		$totalRecords = $searchQuery->count();
		$filteredRecords = $searchQuery->count();

		// Apply search filter
		if (!empty($search_value)) {
			$searchQuery = $this->search($searchQuery, $search_value, ['first_name', 'last_name', 'email', 'phone', 'zip', 'insurance_type', 'is_checked', 'site_name']);
			$filteredRecords = $searchQuery->count();
		}

		$smtps = $searchQuery->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
	
		return datatables()->of($smtps)
			->addIndexColumn()
			->editColumn('is_checked', function ($searchQuery) {
				return $searchQuery->is_checked == 'yes' ? 'Yes' : 'No';
			})
			->addColumn('action', function ($row) {
				return view('fhinsure_logs.partials.buttons-actions', compact('row'));
			})
			->rawColumns(['action'])
			->setTotalRecords($totalRecords)
			->setFilteredRecords($filteredRecords)
			->make(true);
	}

    /**
	 * Display the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id)
	{

		$fhinsureLog = FhinsureLog::findOrFail($id);
		if (!$fhinsureLog) {

			toastr()->error('This data doesn\'t exist');
			return redirect('/newsletter');
		}
		$fhinsureLog = FhinsureLog::where('id', $id)->first();
		return view('fhinsure_logs.show', compact('fhinsureLog'));
	}

    /**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//find the SMTP to delete
		$fhinsureLog = FhinsureLog::find($id);
		if (!$fhinsureLog) {

			toastr()->error('The data was removed previously');
			return back();
		}
		$fhinsureLog->delete();
		toastr()->success('NewsletterDeleted!');
		return  redirect()->route('newsletter.index');
	}

    public function delete_logs(Request $request)
	{
		$logIds = $request->selectedValues;
		if (count($logIds) <= 0) :
			return response()->json(['logIds' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;

		FhinsureLog::whereIn('id', $logIds)->delete();

		return response()->json(['logIds' => 1, 'message' => 'Records deleted successfully']);
	}

	public function contactDetail(Request $request)
	{
		$contactId = $request->singleContactId;
		try {
			$contactDetail = FhinsureLog::withTrashed()->where('id', $contactId)->first();
			if ($contactDetail) {
				return json_encode([
					'status' => 200,
					'message' => 'Contact details showed Successfully',
					'response' => $contactDetail
				]);
			} else {
				return response()->json([
					'success' => 'false',
					'response' => "Failed to show contact detail. Please contact the administrator."
				], 500);
			}
		} catch (\Exception $e) {
			return response()->json([
				'success' => 'false',
				'response' => "Failed to show contact detail. Please contact the administrator.",
				'error'=>$e->getMessage()
			], 500);
		}
	}

	public function send_message(Request $request) {
		$rules = [
			'message_content'  => 'required',
		];

		$niceNames = [
			'message_content' => 'Content',
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
			$validatedData = $validator->validated();
			$input = $request->all();
			$from_user = auth()->user()->id;

			$data['user_id'] = $from_user;
			$data['newsletter_id'] = $input['contact_id'];
			$data['content'] = $input['message_content'];
			$data['chat_type'] = "outbound";
			$data['chat_sms_sent_status'] = 0;
			$data['through_sms_provider_flag'] = 2;

			$contactDetail = FhinsureLog::where('id', $input['contact_id'])->first();
			$data['content'] = str_replace("{CANDIDATE_FIRST_NAME}", $contactDetail->first_name, $data['content']);
			$data['content'] = str_replace("{CANDIDATE_LAST_NAME}", $contactDetail->last_name, $data['content']);
			$data['max_time_to_send'] = Carbon::now();
			
			Message::create($data);

			return response()->json([
				'status' => 200,
				'message' => 'Message sent successfully'
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => 500,
				'response' => "Failed to send the message. Please contact the administrator.",
				'error' =>  $e->getMessage()
			]);
		}
	}

}
