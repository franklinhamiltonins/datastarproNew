<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Model\AgentLog;
use App\Model\ContactStatus;
use App\Model\FhinsureLog;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Log;
use App\Model\SmtpConfiguration;
use App\Traits\CommonFunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Traits\SendSmsToQueueTrait;
use App\Traits\VontageunctionsTrait;
use Carbon\Carbon;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Validator;
use Yajra\Datatables\Datatables;

class ContactController extends Controller
{
    use CommonFunctionsTrait,KlaviyoFunctionsTrait,SendSmsToQueueTrait,VontageunctionsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(
            'permission:lead-list|lead-create|lead-edit|lead-delete|lead-file-list|lead-action'
        )->only(['index', 'contact_store']);

        $this->middleware('permission:lead-create')
            ->only(['create', 'contact_store']);

        $this->middleware('permission:lead-edit')
            ->only(['edit', 'contact_update']);

        $this->middleware('permission:contact-delete')
            ->only(['contact_destroy', 'delete_contacts', 'remove_contacts']);
    }

    public function index() {
        $cityCounts = Contact::select('c_city', DB::raw('COUNT(*) as total')) ->whereNotNull('c_city') ->where('c_city', '!=', '') ->groupBy('c_city') ->orderBy('c_city') ->get();
        return view('contacts.index', compact('cityCounts'));
    }

    public function data(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $start           = $request->input('start', 0);
        $length          = $request->input('length', 10);
        $orderColumnIdx  = $request->input('order.0.column', 0);
        $orderColumnName = $request->input("columns.$orderColumnIdx.data", 'id');
        $orderDirection  = $request->input('order.0.dir', 'desc');
        $searchValue     = $request->input('search.value', '');

        // Base Query
        $baseQuery = Contact::query();

        // Filters
        $baseQuery->when($request->filled('city'), fn ($q) =>
            $q->where('c_city', $request->city)
        );

        $baseQuery->when($request->filled('business_type'), fn ($q) =>
            $q->whereHas('leads', fn ($sub) =>
                $sub->where('type', $request->business_type)
            )
        );

        $baseQuery->when($request->has('c_merge_status'), fn ($q) =>
            $q->whereHas('leads', fn ($sub) =>
                $sub->where('merge_status', $request->c_merge_status)
            )
        );

        $filteredQuery = clone $baseQuery;

        // Global Search
        if ($searchValue !== '') {
            $searchable = [
                'c_full_name', 'c_city', 'c_state', 'c_zip',
                'c_phone', 'c_email', 'contact_slug'
            ];
            $this->search($filteredQuery, $searchValue, $searchable);
        }

        // Final Select
        $data = $filteredQuery
            ->select([
                'id', 'c_full_name', 'c_city', 'c_state', 'c_zip',
                'c_phone', 'c_email', 'c_is_client', 'contact_slug',
                'c_merge_status', 'lead_id'
            ])
            ->with(['leads:id,type,merge_status'])
            ->orderBy($orderColumnName, $orderDirection);

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('c_is_client', fn ($row) => $row->c_is_client ? 'yes' : 'no')
            ->addColumn('action', fn ($row) => $this->actionButtons($row))
            ->rawColumns(['action'])
            ->make(true);
    }
    private function actionButtons($row)
    {
        $btn  = '<div class="d-flex justify-content-center action-btns">';

        if ($row->c_merge_status) {
            $btn .= '<a href="/contacts/merge/'.$row->contact_slug.'"
                        target="_blank" class="btn btn-sm btn-danger action-btn m-0">
                        <i class="fa fa-compress"></i>
                     </a>';
        }

        $btn .= '<a href="/leads/show/'.base64_encode($row->lead_id).'"
                    class="btn btn-sm btn-info action-btn m-0"
                    title="View Contact Lead Record">
                    <i class="fa fa-eye"></i>
                 </a>';

        if (auth()->user()->can('contact-delete')) {
            $btn .= '<a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal"
                        onclick="setDeleteModal(this, '.$row->id.')"
                        class="btn btn-sm btn-danger action-btn m-0">
                        <i class="fa fa-trash"></i>
                     </a>';
        }

        return $btn.'</div>';
    }

    public function updateContactStatus(Request $request)
    {
        $status = $request->status;
        $leadId = (int) $request->lead_id;
        $contactId = (int) $request->contact_id;
        $customstatus = 0;
        $message = 'Required fields are missing. Please contact your administrator!';
        if ($leadId > 0 && $contactId > 0 && ! empty($status)) {
            $contact = Contact::find($contactId);

            $removableStatus = ['Bad Number', 'Do Not Call', 'Not Interested', 'Call Back'];
            if (in_array($status, $removableStatus)) {
                $this->updateDialingLists($status, $contactId, $leadId);
            }
            $contact->update(['status' => $status, 'called_agent_id' => 0, 'agent_call_initiated' => 'no']);
            $message = 'Status updated successfully!';
            $customstatus = 1;
        }

        return json_encode([
            'status' => '200',
            'message' => $message,
            'custom_status' => $customstatus,
        ]);
    }

    // update multiple contact's status
    public function contact_status_update(Request $request, $leadID)
    {
        $contactStatus = $request->c_status;
        $lead = Lead::find($leadID);
        $allContactFromLeadId = Contact::where('lead_id', $leadID)->get();

        $contactStatus = $request->c_status;
        try {
            // CONTACT table update first
            $cAgentId = $request->c_agent_id;
            $agentTypeStatus = null;
            if (! empty($request->c_status)) {
                $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();
                if ($agentTypeStatus && $agentTypeStatus->status_type == 2 && empty($cAgentId)) {
                    toastr()->error('Selecting an agent is mandatory with '.$agentTypeStatus->name.' status');

                    return back()->withInput();
                }
            }
            $updateDone = Contact::where('lead_id', $leadID)->update(['c_status' => $contactStatus, 'c_agent_id' => $cAgentId]);

            if ($updateDone) {
                $ownStatus = $agentTypeStatus->display_in_pipedrive;
                // now updating
                foreach ($allContactFromLeadId as $singleContact) {
                    if ($agentTypeStatus && empty($agentTypeStatus->false_status)) {
                        $this->updateDialingLists($contactStatus, $singleContact->id, $leadID, $cAgentId, $ownStatus);
                        $this->setContactToQueue($lead);

                        $message = auth()->user()->name.' has updated status of contact : '.$singleContact->id.' to '.$contactStatus.' present in lead: '.$leadID;
                        AgentLog::updateOrCreate(
                            ['user_id' => auth()->user()->id, 'contact_id' => $singleContact->id],
                            ['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $leadID, 'contact_id' => $singleContact->id, 'status' => 'call_status_updated']
                        );
                    }
                }

                $this->contactbasedleadstatusupdate($leadID, $cAgentId, $contactStatus);
                toastr()->success('Updated successfully');
            } else {
                toastr()->error('No contacts were updated.');
            }
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
        }

        return back();
    }

    public function merge(Request $request, $slug)
    {
        $contacts = Contact::where('contact_slug', $slug)->get();
        if ($contacts->count() <= 0) {
            toastr()->error('No mergeable contacts exists for above slug.');

            return back();
        }
        $compareArr = [];

        foreach ($contacts as $key => $contact) {
            $columns = $contact->getFillable();
            $contactData = [];
            $attributes = array_diff_key($contact->getAttributes(), array_flip(['c_is_client', 'c_merge_status', 'agent_call_initiated',  'deleted_at', 'has_initiated_stop_chat', 'called_agent_id']));
            foreach ($attributes as $key => $value) {
                $contactData[$key] = $value;
            }
            $compareArr[] = $contactData;
        }

        return view('contacts.merge_contacts', compact('compareArr'));
    }

    public function completemerge(Request $request)
    {
        $payloadData = $request->all();
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function contact_update(Request $request, $id)
    {
        $rules = Contact::rules();
        $niceNames = Contact::niceNames();
        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput(array_merge($request->all(), [
                'contact_id' => $id,
            ]));

        }
        $cAgentId = $request->c_agent_id;
        if (! empty($request->c_status)) {
            $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();
            if ($agentTypeStatus && $agentTypeStatus->status_type == 2 && empty($cAgentId)) {
                toastr()->error('Selecting an agent is mandatory with '.$agentTypeStatus->name.' status');

                return back()->withInput(array_merge($request->all(), [
                    'contact_id' => $id,
                ]));
            }
        }

        $addressWithNumber = $request->c_address1;
        if (preg_match('/\d+/', $request->c_address1, $matches)) {
            $addressWithNumber = $matches[0];
        }
        $contactSlug = $this->generateSlug([$request->c_first_name, $request->c_last_name, $addressWithNumber]);

        $contactExistance = $this->checkContactSlugExistance($contactSlug, $id);
        if (is_array($contactExistance) && isset($contactExistance['existanceCount']) && $contactExistance['existanceCount'] > 0) {
            toastr()->error(implode('</br>', $contactExistance['message']));

            return back()->withErrors($validator)->withInput(array_merge($request->all(), [
                'contact_id' => $id,
            ]));
        }

        $input = $request->all();
        $input['c_agent_id'] = $cAgentId;

        // get the contact to update
        $contact = Contact::find($id);
        $oldcontact = clone $contact;
        if (! $contact) {
            toastr()->error('Something went wrong');

            return back();
        }

        $contact->update($input);
        if (! $request->c_is_client) {
            $contact->update(['c_is_client' => 0]);
        }
        // add fullname and slug
        $contact->update([
            'c_full_name' => $request->c_first_name.' '.$request->c_last_name,
            'contact_slug' => $contactSlug,
        ]);

        // commenting this line for now as no need to send sms and klaviyo for now

        $lead = Lead::find($contact->leads->id);

        // add contact client status as per lead
        if (($lead->is_client == '1')) {
            $contact->update([
                'c_is_client' => 1,
            ]);
        } else {
            $contact->update([
                'c_is_client' => 0,
            ]);
        }
        $this->contactbasedleadstatusupdate($contact->leads->id, $cAgentId, $request->c_status);

        if (! empty($request->c_status) && $agentTypeStatus) {
            $ownStatus = $agentTypeStatus->display_in_pipedrive;
            $this->updateDialingLists($request->c_status, $contact->id, $contact->leads->id, $cAgentId, $ownStatus);
            $this->setContactToQueue($lead);

            $message = auth()->user()->name.' has updated status of contact : '.$contact->id.' to '.$request->c_status.' present in lead: '.$contact->leads->id;
            AgentLog::updateOrCreate(
                ['user_id' => auth()->user()->id, 'contact_id' => $contact->id],
                ['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $contact->leads->id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
            );
        }
        create_log($lead, 'Edit Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');
        toastr()->success('Contact <b>'.$contact->first_name.' '.$contact->last_name.'</b> updated successfully');
        session(['remove_sessionstorage' => 1]);

        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact_store(Request $request, $leadID)
    {
        $rules = Contact::rules();
        $niceNames = Contact::niceNames();
        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            toastr()->error(implode('<br>', $errorMessages));

            return back()->withErrors($validator)->withInput();
        }
        $cAgentId = $request->c_agent_id;
        if (! empty($request->c_status)) {
            $agentTypeStatus = ContactStatus::where('id', $request->c_status)->first();
            if ($agentTypeStatus && $agentTypeStatus->status_type == 2 && empty($cAgentId)) {
                toastr()->error('Selecting an agent is mandatory with '.$agentTypeStatus->name.' status');

                return back()->withInput();
            }
        }
        $addressWithNumber = $request->c_address1;
        if (preg_match('/\d+/', $request->c_address1, $matches)) {
            $addressWithNumber = $matches[0];
        }
        $contactSlug = $this->generateSlug([$request->c_first_name, $request->c_last_name, $addressWithNumber]);

        $contactExistance = $this->checkContactSlugExistance($contactSlug);
        if (is_array($contactExistance) && isset($contactExistance['existanceCount']) && $contactExistance['existanceCount'] > 0) {
            toastr()->error(implode('</br>', $contactExistance['message']));

            return back()->withErrors($validator)->withInput();
        }
        $input = $request->all();
        $input['c_agent_id'] = $cAgentId;

        // create new contact
        $contact = Contact::create($input);
        // add fullname
        $contact->c_full_name = $request->c_first_name.' '.$request->c_last_name;
        $contact->contact_slug = $contactSlug;
        // get the lead where the contact was added
        $lead = Lead::find($leadID);
        // attach the contact to lead
        $contact->leads()->associate($lead); // update the model

        // add contact client status as per lead
        $contact->c_is_client = ($lead->is_client == '1') ? 1 : 0;

        $contact->save();
        // commenting this line for now as no need to send sms and klaviyo for now

        $this->contactbasedleadstatusupdate($leadID, $cAgentId, $request->c_status);

        if (! empty($request->c_status) && $agentTypeStatus && empty($agentTypeStatus->false_status)) {
            $ownStatus = $agentTypeStatus->display_in_pipedrive;
            $this->updateDialingLists($request->c_status, $contact->id, $leadID, $cAgentId, $ownStatus);
            $this->setContactToQueue($lead);

            $message = auth()->user()->name.' has updated status of contact : '.$contact->id.' to '.$request->c_status.' present in lead: '.$contact->leads->id;
            AgentLog::updateOrCreate(
                ['user_id' => auth()->user()->id, 'contact_id' => $contact->id],
                ['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $contact->leads->id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
            );
        }

        create_log($lead, 'Create Contact : '.$contact->c_first_name.' '.$contact->c_last_name, '');
        toastr()->success('Contact <b>'.$contact->first_name.' '.$contact->last_name.'</b> created successfully');

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
        // get the contact to delete
        $contact = Contact::find($id);
        if (! $contact) {

            toastr()->error('The Contact was removed previously');

            return back();
        }
        $name = $contact->c_first_name.' '.$contact->c_last_name;

        if (is_object($contact) && isset($contact->leads->id)) {
            $lead = Lead::find($contact->leads->id);
            create_log($lead, 'Delete Contact : '.$name, '');
        }
        $contact->delete();

        if ($request->ajax()) {
            return response()->json(['contactsCount' => 1, 'message' => 'Contact <b>'.$name.'</b> Deleted successfully!']);
        }
        toastr()->success('Contact <b>'.$name.'</b> Deleted!');

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
        if (count($contactIds) <= 0) {
            return response()->json(['contactsCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
        }

        // Retrieve leads based on the array of IDs
        $contacts = Contact::whereIn('id', $contactIds)->get();
        // Loop through the contacts
        foreach ($contacts as $contact) {
            // Delete all contacts related to the lead
            // create Lead Log
            $leadlog = new Log;
            $leadlog->action = 'Remove Contact : '.$contact->c_full_name;
            $leadlog->users()->associate(auth()->user())->save(); // associate user

            // remove contact
            $contact->delete();
        }

        return response()->json(['contactsCount' => 1, 'message' => 'Records deleted successfully']);
    }

    public function mark_comolete_chat(Request $request)
    {
        $contacts = Contact::where('id', $request->contact_id)->first();
        if ($contacts) {
            $contacts->agent_marked_conversation_ended = 1;
            $contacts->save();
        }

        return response()->json(['success' => true, 'message' => 'Marked conversation as completed']);
    }

    public function mark_stop_chat(Request $request)
    {
        $contacts = Contact::where('id', $request->contact_id)->first();
        if ($contacts) {
            $contacts->has_initiated_stop_chat = 1;
            $contacts->save();
        }

        return response()->json(['success' => true, 'message' => 'stop further conversation on this contact']);
    }

    /**
     * Read data from csv file
     *
     * @param  object  $csvFile
     * @return array $csvData
     */
    private static function readDataFromCsv($csvFile, $extension)
    {
        // store file
        $fileName = Carbon::now()->format('mdYHisu');
        // if the file is xlsx or xls , convert it to csv
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xlsx') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            } elseif ($extension == 'xls') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
            }

            $reader->setReadDataOnly(true);

            $path = '../storage/app/public/uploads/'.$fileName.'.csv';
            $excel = $reader->load($csvFile);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
            // $writer->setUseBOM(true);
            // $writer->setOutputEncoding('UTF-8');
            $writer->setUseBOM(false);
            $writer->setOutputEncoding('UTF-8');
            $writer->setEnclosureRequired(false);
            $writer->save($path);

            $csvFile = $path;
        } else {

            $file = Storage::putFileAs('public/uploads', $csvFile, $fileName.'.csv');
        }

        $delimiter = ',';
        $header = null;
        $csvData = [];
        // the required columns
        $requiredColumns = [
            1 => 'Contact_First_Name',
            2 => 'Contact_Last_Name',
            3 => 'Contact_Address1',
        ];
        // read data and add it to array
        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;
                    // loop trough required columns and if one of them is missing in csv, send error
                    foreach ($requiredColumns as $req) {

                        if (! in_array($req, $header)) {
                            $ColumnError = 'Column '.$req.' is missing. File was not parsed.';

                            return ['errors' => $ColumnError];
                        }
                    }
                } else {

                    if (count($header) > count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), '')), 'UTF-8', 'UTF-8');
                    } elseif (count($header) < count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_slice($rows, 0, count($header))), 'UTF-8', 'UTF-8');
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
            'template_subject' => 'required',
            'template_content' => 'required',
        ];

        $niceNames = [
            'template_subject' => 'Subject',
            'template_content' => 'Content',
        ];
        // validate fields using nice name in error messages
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()->all(),
            ]);
        }
        try {
            $this->setDynamicSmtp();
            $validatedData = $validator->validated();
            $input = $request->all();

            $data['subject'] = $input['template_subject'];
            $data['content'] = $input['template_content'];
            $smtpData = SmtpConfiguration::where('user_id', auth()->user()->id)->first();
            $data['signature_image'] = $smtpData['signature_image'];
            $data['signature_text'] = $smtpData['signature_text'];

            if (str_contains($input['current_path'], 'newsletter')) {
                $contactDetail = FhinsureLog::where('id', $input['contact_id'])->first();
                $data['content'] = str_replace('{CANDIDATE_FIRST_NAME}', $contactDetail->first_name, $data['content']);
                $data['content'] = str_replace('{CANDIDATE_LAST_NAME}', $contactDetail->last_name, $data['content']);
                $toAddress = $contactDetail->email;
                $data['module_name'] = 'newsletter';
                $data['newsletter_id'] = $input['contact_id'];
            } else {
                $contactDetail = Contact::where('id', $input['contact_id'])->with('leads:id,name')->first();
                $data['content'] = str_replace('{CANDIDATE_FIRST_NAME}', $contactDetail->c_first_name, $data['content']);
                $data['content'] = str_replace('{CANDIDATE_LAST_NAME}', $contactDetail->c_last_name, $data['content']);
                $data['content'] = str_replace('{BUSINESS_NAME}', $contactDetail->leads->name, $data['content']);
                $toAddress = $contactDetail->c_email;
                $data['module_name'] = 'contact';
                $data['contact_id'] = $input['contact_id'];
            }

            // Mail::to("suparna.dey@codeclouds.in")->send(new ContactMail($data));
            Mail::to($toAddress)->send(new ContactMail($data));

            $this->saveEmailData($data);

            return response()->json([
                'status' => 200,
                'response' => Config::get('mail'),
                'message' => 'Email sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'response' => 'Failed to send the email. Please contact the administrator.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function testurl()
    {
        if (auth()->user()) {
            $credentials = auth()->user();

            return response()->json([
                'status' => 200,
                'message' => 'auth found',
                'isLoggedIn' => true,
                'credentials' => $credentials,
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'auth not found',
                'isLoggedIn' => false,
                'credentials' => null,
            ]);
        }

        $data['content'] = '<div>
                <p>Hi Rohit,</p><p>We spoke at the trade show regarding your condo insurance.</p><p>Let me know when a good time to call will be.</p><p>Sincerely,</p>
            </div>';

        $data['signature_text'] = '<p>Bisakha Pati</p><p>Agent,Generic Tech Solutions</p><p>Office: <a href="tel:(555) 123-4567">(555) 015-2720</a> Cell: <a href="tel:(555) 010-2020">(555) 123-4567</a></p><p><a href="mailto:Nsledge@fhinsure.com">johndoe@example.com</a></p><p><a href="https://datastarpro.com/smtps/www.fhinsure.com">www.example.com</a></p><p>123 Main Street, Anytown, Anystate, 12345</p>';

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

        return SmtpConfiguration::where('user_id', 20)
            ->where($whereCond)
            ->count();
    }
}
