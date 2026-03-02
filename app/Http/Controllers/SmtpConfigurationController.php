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

/**
 * Controller for managing SMTP configurations
 */
class SmtpConfigurationController extends Controller
{
    use CommonFunctionsTrait;

    /**
     * Constructor - Initialize controller
     */
    public function __construct()
    {
    }

    /**
     * Display SMTP settings page for current user
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get SMTP data for current user
        $smtpData = $this->getUserSmtpData();

        // Get email providers for dropdown
        $emailProviders = $this->getEmailProviders();

        // Handle signature image
        $smtpData = $this->handleSignatureImage($smtpData);

        return view('smtps.smtpsetting_view', compact('smtpData', 'email_providers'));
    }

    /**
     * Get SMTP data for current authenticated user
     * @return SmtpConfiguration
     */
    private function getUserSmtpData(): SmtpConfiguration
    {
        $smtpData = SmtpConfiguration::where('user_id', auth()->user()->id)->first();

        if (!$smtpData) {
            $smtpData = new SmtpConfiguration;
        }

        // Decrypt password for display
        $smtpData['password'] = isset($smtpData['password']) && $smtpData['password']
            ? Crypt::decryptString($smtpData['password'])
            : null;

        return $smtpData;
    }

    /**
     * Get email providers list
     * @return array Email providers with default option
     */
    private function getEmailProviders(): array
    {
        $providers = EmailProvider::get();
        $emailProviders = [0 => 'Select Email Provider'];

        foreach ($providers as $provider) {
            $emailProviders[$provider->id] = $provider->provider_name;
        }

        return $emailProviders;
    }

    /**
     * Handle signature image path
     * @param SmtpConfiguration $smtpData SMTP data
     * @return SmtpConfiguration Updated SMTP data
     */
    private function handleSignatureImage(SmtpConfiguration $smtpData): SmtpConfiguration
    {
        if (isset($smtpData->signature_image) && $smtpData->signature_image
            && file_exists(public_path('images/signature/' . $smtpData->signature_image))) {
            $smtpData->signature_image = '/images/signature/' . $smtpData->signature_image;
        } else {
            $smtpData->signature_image = '/images/placeholder-img.png';
        }

        return $smtpData;
    }

    /**
     * Store SMTP configuration for user
     * @param Request $request HTTP request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = $this->validateStoreRequest($request);

        if ($validator->fails()) {
            return $this->handleValidationFailure($validator);
        }

        // Handle signature image upload
        $imageName = $this->handleSignatureImageUpload($request);

        // Get existing configuration to preserve image
        $imageName = $this->preserveExistingImage($request, $imageName);

        // Save SMTP configuration
        $settingData = $this->saveSmtpConfiguration($request, $imageName);

        // Return response
        return $this->handleStoreResponse($settingData);
    }

    /**
     * Validate store request
     * @param Request $request HTTP request
     * @return \Illuminate\Validation\Validator
     */
    private function validateStoreRequest(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'host' => 'required|max:100',
            'port' => 'required|numeric|min:3',
            'encryption' => 'required',
            'from_name' => 'required|max:100',
            'signature_image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'username' => 'required',
            'password' => 'required',
        ]);
    }

    /**
     * Handle validation failure
     * @param \Illuminate\Validation\Validator $validator Validator instance
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleValidationFailure(\Illuminate\Validation\Validator $validator): \Illuminate\Http\RedirectResponse
    {
        $errorMessages = $validator->errors()->all();
        toastr()->error(implode('<br>', $errorMessages));

        return back()->withErrors($validator)->withInput();
    }

    /**
     * Handle signature image upload
     * @param Request $request HTTP request
     * @return string|null Image name
     */
    private function handleSignatureImageUpload(Request $request): ?string
    {
        $imageName = null;

        if ($request->hasFile('signature_image')) {
            $imageName = time() . '.' . $request->signature_image->extension();

            if (!file_exists(public_path('images/signature'))) {
                mkdir(public_path('images/signature'), 0777, true);
            }

            $request->signature_image->move(public_path('images/signature'), $imageName);
        }

        return $imageName;
    }

    /**
     * Preserve existing image if no new one uploaded
     * @param Request $request HTTP request
     * @param string|null $imageName Current image name
     * @return string|null Preserved or new image name
     */
    private function preserveExistingImage(Request $request, ?string $imageName): ?string
    {
        if (!isset($imageName)) {
            $smtpConfiguration = SmtpConfiguration::where('user_id', $request->user_id)->first();

            if (isset($smtpConfiguration['signature_image']) && $smtpConfiguration['signature_image']) {
                $imageName = $smtpConfiguration['signature_image'];
            } else {
                $imageName = null;
            }
        }

        return $imageName;
    }

    /**
     * Save SMTP configuration
     * @param Request $request HTTP request
     * @param string|null $imageName Image name
     * @return SmtpConfiguration
     */
    private function saveSmtpConfiguration(Request $request, ?string $imageName): SmtpConfiguration
    {
        return SmtpConfiguration::updateOrCreate(
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
    }

    /**
     * Handle store response
     * @param SmtpConfiguration $settingData Saved data
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleStoreResponse(SmtpConfiguration $settingData): \Illuminate\Http\RedirectResponse
    {
        if ($settingData) {
            toastr()->success('Data submitted successfully');
            return redirect()->back();
        } else {
            toastr()->success('Something went wrong!!!');
            return redirect()->back();
        }
    }

    /**
     * Display admin SMTP list page
     * @return \Illuminate\View\View
     */
    public function adminIndex()
    {
        // Get agents without SMTP configuration
        $agentData = $this->getAgentsWithoutSmtp();

        $agentCount = $agentData['agentCount'];
        $agentMsg = $agentData['agentMsg'];

        return view('smtps.index', compact('agentCount', 'agentMsg'));
    }

    /**
     * Get agents without SMTP configuration
     * @return array Agent count and message
     */
    private function getAgentsWithoutSmtp(): array
    {
        $agents = User::role(['Agent', 'Service & Agent', 'Admin', 'Super Admin'])
            ->with('smtp')
            ->orderBy('name', 'asc')
            ->get();

        $agentUsers = [];
        foreach ($agents as $agent) {
            if (is_null($agent->smtp)) {
                $agentUsers[$agent->id] = $agent->name . ' (' . $agent->email . ')';
            }
        }

        $agentCount = count($agentUsers);
        $agentMsg = $agentCount > 0 ? '' : 'No Agent left - SMTP has been configured for all';

        return [
            'agentCount' => $agentCount,
            'agentMsg' => $agentMsg,
            'agentUsers' => $agentUsers,
        ];
    }

    /**
     * Get SMTP records for datatables
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSmtps(Request $request)
    {
        // Extract request parameters
        $params = $this->extractListRequestParams($request);

        // Build base query
        $baseQuery = $this->buildSmtpBaseQuery();

        // Get record counts
        $totalRecords = $baseQuery->count();
        $filteredRecords = $baseQuery->count();

        // Apply search filter if provided
        $searchQuery = clone $baseQuery;
        if (!empty($params['searchValue'])) {
            $filteredRecords = $searchQuery->count();
        }

        // Apply ordering and pagination
        $smtps = $searchQuery
            ->orderBy($params['filterOnColumnName'], $params['orderBy'])
            ->offset($params['start'])
            ->limit($params['length']);

        return datatables()->of($smtps)
            ->addIndexColumn()
            ->editColumn('user_id', function ($query) {
                return !is_null($query->user) ? $query->user->name : '';
            })
            ->editColumn('provider_id', function ($query) {
                return $query->provider_id > 0 ? $query->provider->provider_name : null;
            })
            ->addColumn('action', function ($row) {
                return view('smtps.partials.buttons-actions', compact('row'));
            })
            ->rawColumns(['action'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filteredRecords)
            ->make(true);
    }

    /**
     * Extract list request parameters
     * @param Request $request HTTP request
     * @return array Parameters
     */
    private function extractListRequestParams(Request $request): array
    {
        return [
            'start' => $request->input('start', 0),
            'length' => $request->input('length', 5),
            'filterOnColumnNumber' => $request->input('order')[0]['column'] ?? 1,
            'filterOnColumnName' => $request->input('columns')[$request->input('order')[0]['column'] ?? 1]['name'] ?? 'smtp_configurations.id',
            'orderBy' => $request->input('order')[0]['dir'] ?? 'desc',
            'searchValue' => $request->input('search')['value'] ?? null,
        ];
    }

    /**
     * Build base query for SMTP list
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildSmtpBaseQuery()
    {
        return SmtpConfiguration::with(['provider', 'user'])
            ->whereNull('smtp_configurations.deleted_at');
    }

    /**
     * Delete multiple SMTP configurations
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSmtps(Request $request)
    {
        $smtpIds = $request->selectedValues;

        if (count($smtpIds) <= 0) {
            return response()->json([
                'smtpCount' => 0,
                'message' => 'Please check at least one checkbox to continue.'
            ]);
        }

        SmtpConfiguration::whereIn('id', $smtpIds)->delete();

        return response()->json([
            'smtpCount' => 1,
            'message' => 'Records deleted successfully'
        ]);
    }

    /**
     * Display SMTP configuration details
     * @param Request $request HTTP request
     * @param string $id Encoded SMTP ID
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, string $id)
    {
        $decodedId = base64_decode($id);
        $smtpConfiguration = SmtpConfiguration::findOrFail($decodedId);

        if (!$smtpConfiguration) {
            toastr()->error('This SMTP configuration doesn\'t exist');
            return redirect('/smtps');
        }

        // Get full configuration with relationships
        $smtpConfiguration = $this->getFullSmtpConfiguration($decodedId);

        return view('smtps.show', compact('smtpConfiguration'));
    }

    /**
     * Get full SMTP configuration with relationships
     * @param int $id SMTP ID
     * @return SmtpConfiguration
     */
    private function getFullSmtpConfiguration(int $id): SmtpConfiguration
    {
        $smtpConfiguration = SmtpConfiguration::where('id', $id)
            ->with(['user', 'provider'])
            ->first();

        // Decrypt password
        $smtpConfiguration['password'] = $smtpConfiguration['password']
            ? Crypt::decryptString($smtpConfiguration['password'])
            : null;

        return $smtpConfiguration;
    }

    /**
     * Display create SMTP form
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Check admin status
        $isAdmin = $this->checkAdminStatus();

        // Get agents based on permissions
        $agents = $this->getAgentsForCreation($isAdmin);

        // Filter agents without SMTP
        $agentUsers = $this->filterAgentsWithoutSmtp($agents);

        // Get email providers
        $emailProviders = $this->getEmailProviders();

        
        if (empty($agentUsers)) {
            toastr()->success('No user left - SMTP has been configured for all');
            return redirect('/smtps');
        }

        return view('smtps.create', compact('agentUsers', 'emailProviders'));
    }

    /**
     * Check if current user is admin
     * @return bool
     */
    private function checkAdminStatus(): bool
    {
        return auth()->user()->can('agent-create');
    }

    /**
     * Get agents for SMTP creation based on permissions
     * @param bool $isAdmin Whether user is admin
     * @return \Illuminate\Collection
     */
    private function getAgentsForCreation(bool $isAdmin)
    {
        if ($isAdmin) {
            return User::with('smtp')->orderBy('name', 'asc')->get();
        } else {
            if (auth()->user()->can('all-accounts-list-pipedrive')) {
                return User::role(['Agent', 'Service Team', 'Service & Agent'])
                    ->with('smtp')
                    ->orderBy('name', 'asc')
                    ->get();
            } else {
                return User::role(['Agent', 'Service & Agent'])
                    ->with('smtp')
                    ->orderBy('name', 'asc')
                    ->get();
            }
        }
    }

    /**
     * Filter agents without SMTP configuration
     * @param \Illuminate\Collection $agents Agents collection
     * @return array Filtered agent list
     */
    private function filterAgentsWithoutSmtp($agents): array
    {
        $agentUsers = ['' => 'Select User'];

        foreach ($agents as $agent) {
            if (is_null($agent->smtp)) {
                $agentUsers[$agent->id] = $agent->name . ' ( ' . $agent->email . ' )';
            }
        }

        return $agentUsers;
    }

    /**
     * Store new SMTP configuration (admin)
     * @param Request $request HTTP request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSmtp(Request $request)
    {
        // Build validation rules
        $rules = $this->buildStoreSmtpRules($request);

        // Validate request
        $validator = $this->validateSmtpRequest($request, $rules);

        if ($validator->fails()) {
            return $this->handleValidationFailure($validator);
        }

        // Prepare input data
        $input = $this->prepareSmtpInput($request);

        // Create SMTP configuration
        $smtpConfiguration = SmtpConfiguration::create($input);
        $encodedId = base64_encode($smtpConfiguration->id);

        toastr()->success('SMTP configuration added successfully');

        return redirect()->route('smtps.update', ['id' => $encodedId]);
    }

    /**
     * Build validation rules for SMTP store
     * @param Request $request HTTP request
     * @return array Validation rules
     */
    private function buildStoreSmtpRules(Request $request): array
    {
        $rules = [
            'host' => 'required|max:100',
            'port' => 'required|numeric|min:3',
            'encryption' => 'required',
            'from_name' => 'required|max:100',
            'user_id' => 'required',
            'signature_image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Add username/password rules if user is editing their own config
        if (isset($request->user_id) && $request->user_id == auth()->user()->id) {
            $rules['username'] = 'required';
            $rules['password'] = 'required';
        }

        return $rules;
    }

    /**
     * Validate SMTP request
     * @param Request $request HTTP request
     * @param array $rules Validation rules
     * @return \Illuminate\Validation\Validator
     */
    private function validateSmtpRequest(Request $request, array $rules): \Illuminate\Validation\Validator
    {
        $niceNames = [
            'user_id' => 'User Name',
            'username' => 'Email',
        ];

        return Validator::make($request->all(), $rules, [], $niceNames);
    }

    /**
     * Prepare input data for SMTP
     * @param Request $request HTTP request
     * @return array Input data
     */
    private function prepareSmtpInput(Request $request): array
    {
        $input = $request->all();

        // Handle signature image upload
        if ($request->hasFile('signature_image')) {
            $imageName = time() . '.' . $request->signature_image->extension();

            if (!file_exists(public_path('images/signature'))) {
                mkdir(public_path('images/signature'), 0777, true);
            }

            $request->signature_image->move(public_path('images/signature'), $imageName);
            $input['signature_image'] = $imageName;
        }

        // Encrypt password
        $input['password'] = Crypt::encryptString($input['password']);

        return $input;
    }

    /**
     * Display edit SMTP form
     * @param string $id Encoded SMTP ID
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Request $request)
    {
        $id = $request->route('id'); 
        $decodedId = base64_decode($id);
        $smtpConfiguration = SmtpConfiguration::where('id', $decodedId)->with('user')->first();

        if (!$smtpConfiguration) {
            toastr()->error('This SMTP configuration doesn\'t exist');
            return redirect('/smtps');
        }

        // Get email providers
        $emailProviders = $this->getEmailProviders();

        // Decrypt password
        $smtpConfiguration['password'] = $smtpConfiguration['password']
            ? Crypt::decryptString($smtpConfiguration['password'])
            : null;

        // Handle signature image
        $smtpConfiguration = $this->handleSignatureImage($smtpConfiguration);

        return view('smtps.edit', compact('smtpConfiguration', 'emailProviders'));
    }

    /**
     * Update SMTP configuration
     * @param Request $request HTTP request
     * @param int $id SMTP ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $smtpConfiguration = SmtpConfiguration::find($id);

        if (!$smtpConfiguration) {
            toastr()->error('Something went wrong');
            return back();
        }

        // Build update rules
        $rules = $this->buildUpdateRules($smtpConfiguration);

        // Validate request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->handleValidationFailure($validator);
        }

        // Prepare input data
        $input = $this->prepareUpdateInput($request, $smtpConfiguration);

        // Update configuration
        $smtpConfiguration->update($input);
        toastr()->success('SMTP configuration updated successfully');

        return redirect()->back();
    }

    /**
     * Build validation rules for update
     * @param SmtpConfiguration $smtpConfiguration SMTP configuration
     * @return array Validation rules
     */
    private function buildUpdateRules(SmtpConfiguration $smtpConfiguration): array
    {
        $rules = [
            'host' => 'required|max:100',
            'port' => 'required|numeric|min:3',
            'encryption' => 'required',
            'from_name' => 'required|max:100',
            'signature_image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Require password if user is updating their own config
        if ($smtpConfiguration->user_id == auth()->user()->id) {
            $rules['password'] = 'required';
        }

        return $rules;
    }

    /**
     * Prepare input data for update
     * @param Request $request HTTP request
     * @param SmtpConfiguration $smtpConfiguration SMTP configuration
     * @return array Input data
     */
    private function prepareUpdateInput(Request $request, SmtpConfiguration $smtpConfiguration): array
    {
        $input = $request->all();

        // Handle signature image upload
        if ($request->hasFile('signature_image')) {
            $imageName = time() . '.' . $request->signature_image->extension();

            if (!file_exists(public_path('images/signature'))) {
                mkdir(public_path('images/signature'), 0777, true);
            }

            $request->signature_image->move(public_path('images/signature'), $imageName);
        }

        // Encrypt password if provided
        if (!empty($input['password'])) {
            $input['password'] = Crypt::encryptString($input['password']);
        }

        // Preserve existing image if not updated
        if (!isset($imageName)) {
            if (isset($smtpConfiguration['signature_image']) && $smtpConfiguration['signature_image']) {
                $imageName = $smtpConfiguration['signature_image'];
            } else {
                $imageName = null;
            }
        }

        $input['signature_image'] = $imageName;

        return $input;
    }

    /**
     * Delete SMTP configuration
     * @param int $id SMTP ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $smtpConfiguration = SmtpConfiguration::find($id);

        if (!$smtpConfiguration) {
            toastr()->error('The SMTP was removed previously');
            return back();
        }

        $smtpConfiguration->delete();
        toastr()->success('SMTP Deleted!');

        return redirect()->route('smtps.index');
    }

    /**
     * Add sample emails to Klaviyo for testing
     */
    public function addEmailtoKlaviyo()
    {
        // Generate random number of contacts to process
        $length = $this->generateSecureRandomNumber(1, 3);

        // Get contacts with mailinator.com emails
        $contacts = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated')
            ->where([
                ['c_email', 'like', '%mailinator.com%'],
                ['c_email', '!=', null],
                ['klaviyo_call_initiated', '=', 0],
            ])
            ->limit($length)
            ->get();

        // Queue jobs for each contact
        $ids = $this->queueKlaviyoJobs($contacts);

        // Update contacts that were queued
        if (!empty($ids)) {
            Contact::whereIn('id', $ids)->update(['klaviyo_call_initiated' => 1]);
        }
    }

    /**
     * Queue Klaviyo jobs for contacts
     * @param \Illuminate\Collection $contacts Contacts collection
     * @return array Contact IDs
     */
    private function queueKlaviyoJobs($contacts): array
    {
        $ids = [];

        foreach ($contacts as $contact) {
            $ids[] = $contact->id;
            $delay = $this->generateSecureRandomNumber(1, 60);

            AddEmailToKlaviyo::dispatch($contact)
                ->delay(now()->addSeconds($delay));
        }

        return $ids;
    }
}
