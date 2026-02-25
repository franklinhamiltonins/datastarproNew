<?php

namespace App\Http\Controllers;

use App\Jobs\AddEmailToKlaviyo;
use App\Model\EmailProvider;
use App\Model\LeadsModel\Contact;
use App\Model\SmtpConfiguration;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class SmtpConfigurationController extends Controller
{
    use CommonFunctionsTrait;

    public function __construct()
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

        $smtpData = SmtpConfiguration::where('user_id', auth()->user()->id)->first();
        if (! $smtpData) {
            $smtpData = new SmtpConfiguration;
        }
        $smtpData['password'] = isset($smtpData['password']) && $smtpData['password'] ? Crypt::decryptString($smtpData['password']) : null;
        $providers = EmailProvider::get();
        $email_providers = [];
        $email_providers[0] = 'Select Email Provider';

        foreach ($providers as $provider) {
            $email_providers[$provider->id] = $provider->provider_name;
        }

        if (isset($smtpData->signature_image) && $smtpData->signature_image && file_exists(public_path('images/signature/'.$smtpData->signature_image))) {
            $smtpData->signature_image = '/images/signature/'.$smtpData->signature_image;
        } else {
            $smtpData->signature_image = '/images/placeholder-img.png';
        }

        return view('smtps.smtpsetting_view', compact('smtpData', 'email_providers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'host' => 'required|max:100',
            'port' => 'required|numeric|min:3',
            'encryption' => 'required',
            'from_name' => 'required|max:100',
            'signature_image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('signature_image')) {
            // $image_name_with_extension = $request->file('signature_image')->getClientOriginalName();
            $imageName = time().'.'.$request->signature_image->extension();
            if (! file_exists(public_path('images/signature'))) {
                mkdir(public_path('images/signature'), 0777, true);
            }
            $request->signature_image->move(public_path('images/signature'), $imageName);
        }

        $smtpConfiguration = SmtpConfiguration::where('user_id', $request->user_id)->first();
        if (! isset($imageName)) {
            if (isset($smtpConfiguration['signature_image']) && $smtpConfiguration['signature_image']) {
                $imageName = $smtpConfiguration['signature_image'];
            } else {
                $imageName = null;
            }
        }
        $settingData = SmtpConfiguration::updateOrCreate(

            ['user_id' => $request->user_id],
            [
                'provider_id' => $request->provider_id,
                'host' => $request->host,
                'port' => $request->port,
                'encryption' => $request->encryption,
                'username' => $request->username ?? null,
                'password' => $request->password ? Crypt::encryptString($request->password) : null,
                'from_name' => $request->from_name,
                'auth' => $request->auth,
                'user_id' => $request->user_id,
                'signature_image' => $imageName,
                'signature_text' => $request->signature_text,
            ]
        );

        if ($settingData) {
            toastr()->success('Data submitted successfully');

            return redirect()->back();
        } else {
            toastr()->success('Something went wrong!!!');

            return redirect()->back();
        }
    }

    public function adminIndex()
    {
        $agents = User::role(['Agent', 'Service & Agent', 'Admin', 'Super Admin'])->with('smtp')->orderBy('name', 'asc')->get();
        $agentUsers = [];

        foreach ($agents as $agent) {
            if (is_null($agent->smtp)) {
                $agentUsers[$agent->id] = $agent->name.' ('.$agent->email.')';
            }
        }
        $agent_count = count($agentUsers);
        if ($agent_count > 0) {
            $agent_msg = '';
        } else {
            $agent_msg = 'No Agent left - SMTP has been configured for all';
        }

        return view('smtps.index', compact('agent_count', 'agent_msg'));
    }

    public function get_smtps(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 5); // Default length or adjust as needed
        $filter_on_column_number = $request->input('order')[0]['column'] ?? 1;
        $filter_on_column_name = $request->input('columns')[$filter_on_column_number]['name'] ?? 'smtp_configurations.id';
        $order_by = $request->input('order')[0]['dir'] ?? 'desc';
        $search_value = $request->input('search')['value'] ?? null;

        $baseQuery = SmtpConfiguration::with(['provider', 'user'])
        // ->leftJoin('users', 'smtp_configurations.user_id', '=', 'users.id')
            ->whereNull('smtp_configurations.deleted_at');
        // ->whereNull('users.deleted_at'); // Ensure deleted users are not considered

        $totalRecords = $baseQuery->count();
        $filteredRecords = $baseQuery->count();

        $searchQuery = clone $baseQuery;

        // Apply search filter
        if (! empty($search_value)) {
            $filteredRecords = $searchQuery->count();
        }

        $smtps = $searchQuery->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);

        return datatables()->of($smtps)
            ->addIndexColumn()
            ->editColumn('user_id', function ($searchQuery) {
                return ! is_null($searchQuery->user) ? $searchQuery->user->name : '';
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
        if (count($smtpIds) <= 0) {
            return response()->json(['smtpCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
        }

        SmtpConfiguration::whereIn('id', $smtpIds)->delete();

        return response()->json(['smtpCount' => 1, 'message' => 'Records deleted successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $id = base64_decode($id);
        $smtpConfiguration = SmtpConfiguration::findOrFail($id);
        if (! $smtpConfiguration) {
            toastr()->error('This SMTP configuration doesn\'t exist');

            return redirect('/smtps');
        }
        $smtpConfiguration = SmtpConfiguration::where('id', $id)->with(['user', 'provider'])->first();
        $smtpConfiguration['password'] = $smtpConfiguration['password'] ? Crypt::decryptString($smtpConfiguration['password']) : null;

        return view('smtps.show', compact('smtpConfiguration'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $is_admin = auth()->user()->can('agent-create');
        if ($is_admin) {
            $agents = User::with('smtp')->orderBy('name', 'asc')->get();
        } else {
            if (auth()->user()->can('all-accounts-list-pipedrive')) {
                $agents = User::role(['Agent', 'Service Team', 'Service & Agent'])->with('smtp')->orderBy('name', 'asc')->get();
            } else {
                $agents = User::role(['Agent', 'Service & Agent'])->with('smtp')->orderBy('name', 'asc')->get();
            }
        }
        $agentUsers = [];
        $agentUsers[''] = 'Select User';

        foreach ($agents as $agent) {
            if (is_null($agent->smtp)) {
                $agentUsers[$agent->id] = $agent->name.' ( '.$agent->email.' )';
            }
        }

        $agent_count = count($agentUsers);
        $agent_msg = 'No user left - SMTP has been configured for all';
        if ($agent_count < 0) {
            toastr()->success($agent_msg);

            return redirect('/smtps');
        }
        $providers = EmailProvider::get();
        $email_providers = [];
        $email_providers[0] = 'Select Email Provider';

        foreach ($providers as $provider) {
            $email_providers[$provider->id] = $provider->provider_name;
        }

        return view('smtps.create', compact('agentUsers', 'email_providers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeSmtp(Request $request)
    {
        $rules = [
            'host' => 'required|max:100',
            'port' => 'required|numeric|min:3',
            'encryption' => 'required',
            'from_name' => 'required|max:100',
            'user_id' => 'required',
            'signature_image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];
        if (isset($request->user_id) && $request->user_id == auth()->user()->id) {
            $rules['username'] = 'required';
            $rules['password'] = 'required';
        }

        $niceNames = [
            'user_id' => 'User Name',
            'username' => 'Email',
        ];
        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput();
        }

        $input = $request->all();

        if ($request->hasFile('signature_image')) {
            $imageName = time().'.'.$request->signature_image->extension();
            if (! file_exists(public_path('images/signature'))) {
                mkdir(public_path('images/signature'), 0777, true);
            }
            $request->signature_image->move(public_path('images/signature'), $imageName);
            $input['signature_image'] = $imageName;
        }

        $input['password'] = Crypt::encryptString($input['password']);
        $smtpConfiguration = SmtpConfiguration::create($input);
        $id = base64_encode($smtpConfiguration->id);

        toastr()->success('SMTP configuration added successfully');

        return redirect()->route('smtps.update', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = base64_decode($id);
        $smtpConfiguration = SmtpConfiguration::where('id', $id)->with('user')->first();
        if (! $smtpConfiguration) {
            toastr()->error('This SMTP configuration doesn\'t exist');

            return redirect('/smtps');
        }
        $providers = EmailProvider::get();
        $email_providers = [];
        $email_providers[0] = 'Select Email Provider';

        foreach ($providers as $provider) {
            $email_providers[$provider->id] = $provider->provider_name;
        }
        $smtpConfiguration['password'] = $smtpConfiguration['password'] ? Crypt::decryptString($smtpConfiguration['password']) : null;

        if ($smtpConfiguration->signature_image && file_exists(public_path('images/signature/'.$smtpConfiguration->signature_image))) {
            $smtpConfiguration->signature_image = '/images/signature/'.$smtpConfiguration->signature_image;
        } else {
            $smtpConfiguration->signature_image = '/images/placeholder-img.png';
        }

        return view('smtps.edit', compact('smtpConfiguration', 'email_providers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $smtpConfiguration = SmtpConfiguration::find($id);
        if (! $smtpConfiguration) {
            toastr()->error('Something went wrong');

            return back();
        }

        $rules = [
            'host' => 'required|max:100',
            'port' => 'required|numeric|min:3',
            'encryption' => 'required',
            'from_name' => 'required|max:100',
            'signature_image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];
        if ($smtpConfiguration->user_id == auth()->user()->id) {
            $rules['password'] = 'required';
        }

        $niceNames = [];
        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput();
        }

        $input = $request->all();

        if ($request->hasFile('signature_image')) {
            $imageName = time().'.'.$request->signature_image->extension();
            if (! file_exists(public_path('images/signature'))) {
                mkdir(public_path('images/signature'), 0777, true);
            }
            $request->signature_image->move(public_path('images/signature'), $imageName);
        }

        // if pass is not empty, update it
        if (! empty($input['password'])) {
            $input['password'] = Crypt::encryptString($input['password']);
        }

        if (! isset($imageName)) {
            if (isset($smtpConfiguration['signature_image']) && $smtpConfiguration['signature_image']) {
                $imageName = $smtpConfiguration['signature_image'];
            } else {
                $imageName = null;
            }
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
        // find the SMTP to delete
        $smtpConfiguration = SmtpConfiguration::find($id);
        if (! $smtpConfiguration) {
            toastr()->error('The SMTP was removed previously');

            return back();
        }
        $smtpConfiguration->delete();
        toastr()->success('SMTP Deleted!');

        return redirect()->route('smtps.index');
    }

    public function addEmailtoKlaviyo()
    {
        $length = $this->generateSecureRandomNumber(1, 3);

        $contacts = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated')->where([
            ['c_email', 'like', '%mailinator.com%'],
            ['c_email', '!=', null],
            ['klaviyo_call_initiated', '=', 0],
        ])->limit($length)->get();

        $ids = [];
        foreach ($contacts as $contact) {
            $ids[] = $contact->id;
            $delay = $this->generateSecureRandomNumber(1, 60); // Random delay between 1 second and 5 minutes
            AddEmailToKlaviyo::dispatch($contact)
                ->delay(now()->addSeconds($delay));
        }
        if (! empty($ids)) {
            Contact::whereIn('id', $ids)->update(['klaviyo_call_initiated' => 1]);
        }

    }
}
