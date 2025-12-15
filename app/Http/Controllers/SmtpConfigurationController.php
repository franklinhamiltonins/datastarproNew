<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use Redirect, Response;
use DataTables;
use App\Model\SmtpConfiguration;
use Hash;
use Illuminate\Support\Arr;
use App\Model\EmailProvider;
use Illuminate\Support\Facades\Crypt;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Traits\CommonFunctionsTrait;


use App\Model\LeadsModel\Contact;
use App\Jobs\AddEmailToKlaviyo;

class SmtpConfigurationController extends Controller
{
	use CommonFunctionsTrait;

	function __construct()
	{
		// $this->middleware('permission:agent-create', ['only' => ['adminIndex', 'get_smtps', 'show', 'edit', 'destroy', 'create', 'delete_smtps', 'update']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		$smtp_data = SmtpConfiguration::where('user_id', auth()->user()->id)->first();
		if (!$smtp_data) {
			$smtp_data = new SmtpConfiguration;
			// toastr()->error('No smtp record exists for the account.Please contact system administrator!');
			// return redirect()->route('dashboard');
		}
		$smtp_data['password'] = isset($smtp_data['password']) && $smtp_data['password'] ? Crypt::decryptString($smtp_data['password']) : null;
		$providers = EmailProvider::get();
		$email_providers = [];
		$email_providers[0] = 'Select Email Provider';

		foreach ($providers as $key => $provider) {
			$email_providers[$provider->id] = $provider->provider_name;
		}

		if (isset($smtp_data->signature_image) && $smtp_data->signature_image && file_exists(public_path('images/signature/' . $smtp_data->signature_image))) {
			$smtp_data->signature_image = '/images/signature/' . $smtp_data->signature_image;
		} else {
			$smtp_data->signature_image = '/images/placeholder-img.png';
		}

		return view('smtps.smtpsetting_view', compact('smtp_data', 'email_providers'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'host'       => 'required|max:100',
			'port'       => 'required|numeric|min:3',
			'encryption' => 'required',
			'from_name'  => 'required|max:100',
			'signature_image' 	=> 'image|mimes:jpeg,png,jpg|max:2048',
			'username'   => 'required',
			'password'   => 'required',
		]);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		if ($request->hasFile('signature_image')) {
			// $image_name_with_extension = $request->file('signature_image')->getClientOriginalName();
			$imageName = time() . '.' . $request->signature_image->extension();
			if (!file_exists(public_path('images/signature'))) {
				mkdir(public_path('images/signature'), 0777, true);
			}
			$request->signature_image->move(public_path('images/signature'), $imageName);
		}

		$smtpConfiguration = SmtpConfiguration::where('user_id', $request->user_id)->first();
		if (!isset($imageName)) {
			if (isset($smtpConfiguration['signature_image']) && $smtpConfiguration['signature_image'])
				$imageName = $smtpConfiguration['signature_image'];
			else
				$imageName = null;
		}
		// dd($smtpConfiguration);
		$settingData = SmtpConfiguration::updateOrCreate(

			['user_id'    => $request->user_id],
			[
				'provider_id' => $request->provider_id,
				'host'        => $request->host,
				'port'        => $request->port,
				'encryption'  => $request->encryption,
				'username'    => $request->username ?? NULL,
				'password'    => $request->password ? Crypt::encryptString($request->password) : NULL,
				'from_name'   => $request->from_name,
				'auth'        => $request->auth,
				'user_id'     => $request->user_id,
				'signature_image' => $imageName,
				'signature_text'  => $request->signature_text
			]
		);

		if ($settingData) {
			toastr()->success("Data submitted successfully");
			return redirect()->back();
		} else {
			toastr()->success("Something went wrong!!!");
			return redirect()->back();
		}
	}

	public function adminIndex()
	{
		$agents = User::role(['Agent','Service & Agent', 'Admin', 'Super Admin'])->with('smtp')->orderBy('name', 'asc')->get();
		$agent_users = [];

		foreach ($agents as $key => $agent) {
			if (is_null($agent->smtp)) // List agents who's SMTP is not added
				$agent_users[$agent->id] = $agent->name . ' (' . $agent->email . ')';
		}
		$agent_count = count($agent_users);
		if ($agent_count > 0)
			$agent_msg = "";
		else
			$agent_msg = "No Agent left - SMTP has been configured for all";
		return view('smtps.index', compact('agent_count', 'agent_msg'));
	}

	public function get_smtps(Request $request)
	{
		$start = $request->input('start', 0);
		$length = $request->input('length', 5); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'] ?? 1;
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['name'] ?? 'smtp_configurations.id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;

		// $searchQuery = SmtpConfiguration::with('provider')->orWhereHas('user', function ($query) {
		// 	$query->where('deleted_at', null);
		// });

		$baseQuery = SmtpConfiguration::with(['provider', 'user'])
	    // ->leftJoin('users', 'smtp_configurations.user_id', '=', 'users.id')
	    ->whereNull('smtp_configurations.deleted_at');
	    // ->whereNull('users.deleted_at'); // Ensure deleted users are not considered


		$totalRecords = $baseQuery->count();
		$filteredRecords = $baseQuery->count();

		$searchQuery = clone $baseQuery;

		// Apply search filter
		if (!empty($search_value)) {
			// $searchQuery = $this->search($searchQuery, $search_value, ['host', 'port', 'username', 'encryption', 'from_name', 'users.name', 'provider.provider_name']);
			// $searchQuery = $this->search($searchQuery, $search_value, [
			//     'smtp_configurations.host',
			//     'smtp_configurations.port',
			//     'smtp_configurations.username',
			//     'smtp_configurations.encryption',
			//     'smtp_configurations.from_name',
			//     'users.name',
			//     'email_providers.provider_name'
			// ]);

			$filteredRecords = $searchQuery->count();
		}

		$smtps = $searchQuery->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
	
		return datatables()->of($smtps)
			->addIndexColumn()
			->editColumn('user_id', function ($searchQuery) {
				return !is_null($searchQuery->user) ? $searchQuery->user->name : "";
			})
			->editColumn('provider_id', function ($searchQuery) {
				return $searchQuery->provider_id > 0 ? $searchQuery->provider->provider_name : null;
			})
			->addColumn('action', function ($row) {
				return view('smtps.partials.buttons-actions', compact('row'));
			})
			->rawColumns(['action'])
			->setTotalRecords($totalRecords)
			->setFilteredRecords($filteredRecords)
			->make(true);
	}

	public function delete_smtps(Request $request)
	{
		$smtpIds = $request->selectedValues;
		if (count($smtpIds) <= 0) :
			return response()->json(['smtpCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;

		SmtpConfiguration::whereIn('id', $smtpIds)->delete();

		return response()->json(['smtpCount' => 1, 'message' => 'Records deleted successfully']);
	}

	/**
	 * Display the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id)
	{
		$id = base64_decode($id);
		$smtpConfiguration = SmtpConfiguration::findOrFail($id);
		if (!$smtpConfiguration) {

			toastr()->error('This SMTP configuration doesn\'t exist');
			return redirect('/smtps');
		}
		$smtpConfiguration = SmtpConfiguration::where('id', $id)->with(['user', 'provider'])->first();
		$smtpConfiguration['password'] = $smtpConfiguration['password'] ? Crypt::decryptString($smtpConfiguration['password']) : null;
		return view('smtps.show', compact('smtpConfiguration'));
	}

	/**
	 * Show the form for creating a new resource.
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$is_admin = auth()->user()->can('agent-create');
		if($is_admin){
			$agents = User::with('smtp')->orderBy('name', 'asc')->get();
		}
		else{
			if(auth()->user()->can('all-accounts-list-pipedrive')){
				$agents = User::role(['Agent','Service Team','Service & Agent'])->with('smtp')->orderBy('name', 'asc')->get();
			}
			else{
				$agents = User::role(['Agent','Service & Agent'])->with('smtp')->orderBy('name', 'asc')->get();
			}
		}
		$agent_users = [];
		$agent_users[''] = 'Select User';

		foreach ($agents as $key => $agent) {
			if (is_null($agent->smtp)) // List agents who's SMTP is not added
				$agent_users[$agent->id] = $agent->name . ' ( ' . $agent->email . ' )';
		}
		
		$agent_count = count($agent_users);
		$agent_msg = "No user left - SMTP has been configured for all";
		if ($agent_count < 0) {
			toastr()->success($agent_msg);
			return redirect('/smtps');
		}
		$providers = EmailProvider::get();
		$email_providers = [];
		$email_providers[0] = 'Select Email Provider';

		foreach ($providers as $key => $provider) {
			$email_providers[$provider->id] = $provider->provider_name;
		}

		return view('smtps.create', compact('agent_users', 'email_providers'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function storeSmtp(Request $request)
	{
		$rules = [
			'host'       => 'required|max:100',
			'port'       => 'required|numeric|min:3',
			'encryption' => 'required',
			'from_name'  => 'required|max:100',
			'user_id'    => 'required',
			'signature_image' 	=> 'image|mimes:jpeg,png,jpg|max:2048'
		];
		if(isset($request->user_id) && $request->user_id == auth()->user()->id) {
			$rules['username'] = 'required';
			$rules['password'] = 'required';
		}

		$niceNames = [
			'user_id' => 'User Name',
			'username' => 'Email'
		];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();

		if ($request->hasFile('signature_image')) {
			// $image_name_with_extension = $request->file('signature_image')->getClientOriginalName();
			$imageName = time() . '.' . $request->signature_image->extension();
			if (!file_exists(public_path('images/signature'))) {
				mkdir(public_path('images/signature'), 0777, true);
			}
			$request->signature_image->move(public_path('images/signature'), $imageName);
			$input['signature_image'] = $imageName;
		}

		$input['password'] = Crypt::encryptString($input['password']);
		// $input['password'] = Hash::make($input['password']);
		$smtpConfiguration = SmtpConfiguration::create($input);
		$id = base64_encode($smtpConfiguration->id);

		toastr()->success('SMTP configuration added successfully');
		return redirect()->route('smtps.update', compact('id'));
	}


	/**
	 * Show the form for editing the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$id = base64_decode($id);
		$smtpConfiguration = SmtpConfiguration::where('id', $id)->with('user')->first();
		if (!$smtpConfiguration) {
			toastr()->error('This SMTP configuration doesn\'t exist');
			return redirect('/smtps');
		}
		// echo "<pre>";print_r($smtpConfiguration);exit;
		$providers = EmailProvider::get();
		$email_providers = [];
		$email_providers[0] = 'Select Email Provider';

		foreach ($providers as $key => $provider) {
			$email_providers[$provider->id] = $provider->provider_name;
		}
		$smtpConfiguration['password'] = $smtpConfiguration['password'] ? Crypt::decryptString($smtpConfiguration['password']) : null;

		if ($smtpConfiguration->signature_image && file_exists(public_path('images/signature/' . $smtpConfiguration->signature_image))) {
			$smtpConfiguration->signature_image = '/images/signature/' . $smtpConfiguration->signature_image;
		} else {
			$smtpConfiguration->signature_image = '/images/placeholder-img.png';
		}

		return view('smtps.edit', compact('smtpConfiguration', 'email_providers'));
	}

	/**
	 * Update the specified resource in storage.
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{

		$smtpConfiguration = SmtpConfiguration::find($id);
		if (!$smtpConfiguration) {
			toastr()->error('Something went wrong');
			return back();
		}

		$rules = [
			'host'       		=> 'required|max:100',
			'port'       	 	=> 'required|numeric|min:3',
			'encryption' 		=> 'required',
			'from_name'  		=> 'required|max:100',
			'signature_image' 	=> 'image|mimes:jpeg,png,jpg|max:2048'
		];
		if($smtpConfiguration->user_id == auth()->user()->id) {
			$rules['password'] = 'required';
		}

		$niceNames = [];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();

		if ($request->hasFile('signature_image')) {
			// $image_name_with_extension = $request->file('signature_image')->getClientOriginalName();
			$imageName = time() . '.' . $request->signature_image->extension();
			if (!file_exists(public_path('images/signature'))) {
				mkdir(public_path('images/signature'), 0777, true);
			}
			$request->signature_image->move(public_path('images/signature'), $imageName);
		}

		//if pass is not empty, update it
		if (!empty($input['password'])) {
			$input['password'] = Crypt::encryptString($input['password']);
		}

		if (!isset($imageName)) {
			if (isset($smtpConfiguration['signature_image']) && $smtpConfiguration['signature_image'])
				$imageName = $smtpConfiguration['signature_image'];
			else
				$imageName = null;
		}
		$input['signature_image'] = $imageName;

		$smtpConfiguration->update($input);
		toastr()->success('SMTP configuration updated successfully');
		return redirect()->back();
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
		$smtpConfiguration = SmtpConfiguration::find($id);
		if (!$smtpConfiguration) {

			toastr()->error('The SMTP was removed previously');
			return back();
		}
		$smtpConfiguration->delete();
		toastr()->success('SMTP Deleted!');
		return  redirect()->route('smtps.index');
	}

	public function addEmailtoKlaviyo() 
	{
		$length = rand(1, 3);
		$contacts_sql = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated')->where([
			['c_email', 'like', '%mailinator.com%'],
			['c_email', '!=', NULL ],
			['klaviyo_call_initiated', '=', 0],
		])->limit($length)->toSql();

		echo $contacts_sql;

		$contacts = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated')->where([
			['c_email', 'like', '%mailinator.com%'],
			['c_email', '!=', NULL ],
			['klaviyo_call_initiated', '=', 0],
		])->limit($length)->get();

		echo "<br>";
		// dd($contacts);
		$ids = [];
		foreach($contacts as $contact){
			$ids[] = $contact -> id;
			$delay = rand(1, 60); // Random delay between 1 second and 5 minutes
			echo('----'.$contact -> id."---".$delay);
			echo "<br>";
			AddEmailToKlaviyo::dispatch($contact)
				->delay(now()->addSeconds($delay));
			// $apiKey = config('app.klaviyo_private_key');
            // $list_id = config('app.klaviyo_list_id');
			// // echo $list_id."----<br>";
            // $data = array (
            //     'profiles' => array ('0' => array ( 
            //         'email' => $contact->c_email,
            //         // "phone_number"=> "+1".$phone_number,
            //         'first_name' => $contact->c_first_name,
            //         'last_name'  => $contact->c_last_name,
            //         'location' => ['zip' => $contact->c_zip],
            //     )),
            // );
        
            // $curl = curl_init();
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => "https://a.klaviyo.com/api/v2/list/".$list_id."/members?api_key=".$apiKey,
            //     CURLOPT_RETURNTRANSFER => true,
			// 	CURLOPT_SSL_VERIFYPEER => false,
            //     CURLOPT_CUSTOMREQUEST => "POST",
            //     CURLOPT_POSTFIELDS => json_encode($data),
            //     CURLOPT_HTTPHEADER => array(
            //         // "Authorization: Klaviyo-API-Key $apiKey",
            //         "cache-control: no-cache",
            //         "content-type: application/json",
            //     ),
            // ));
        
            // $response = curl_exec($curl);
            // $err = curl_error($curl);
            // curl_close($curl);
            // $result = json_decode($response);
			// print_r($err);
			// dd($result);
		}
		if(!empty($ids))
			Contact::whereIn('id', $ids)->update(['klaviyo_call_initiated' => 1]);
			
	}
}
