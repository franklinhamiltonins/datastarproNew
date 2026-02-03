<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Model\User;
use DB;
use Hash;
use App\Model\ActivityReportFile;
use App\Model\ActivityReportAor;
use App\Model\ActivityReport;
use App\Model\MailerLeadTracker;
use App\Model\LeadSource;
use App\Model\DailyCallReportLog;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Note;
use App\Model\ContactStatus;
use App\Model\Agentlog;

use App\Traits\CommonFunctionsTrait;
use App\Traits\ActivityReportTrait;
use App\Traits\SMTPRelatedTrait;

use App\Jobs\ActivityReportDownload;
use App\Jobs\ProcessMailDailyCallReportJob;
use App\Jobs\ProcessMailerLeadTrackerReportJob;


use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use App\Model\MailFetchedLog;
use App\Model\Setting;

class ActivityReportController extends Controller
{
    use CommonFunctionsTrait,ActivityReportTrait,SMTPRelatedTrait;

    function __construct()
    {

        $this->middleware('permission:report-activity-form', ['only' => ['activity', 'saveAgentActivity']]);
        $this->middleware('permission:report-mailer-lead-form', ['only' => ['mailerleadtracker', 'saveMailLeadTracker']]);

        $this->middleware('permission:report-activity-result', ['only' => ['activityReport', 'activityList']]);
        $this->middleware('permission:report-mailer-lead-result', ['only' => ['mailerLeadReport', 'mailLeadTrackerList']]);
        $this->middleware('permission:report-daily-call-result', ['only' => ['daillyCallReport', 'dailycallReportList']]);

        $this->middleware('permission:report-activity-download', ['only' => [ 'activityListDownload']]);
        $this->middleware('permission:report-mailer-lead-download', ['only' => [ 'mailLeadTrackerListDownload']]);
        $this->middleware('permission:report-daily-call-download', ['only' => [ 'dailycallReportListDownload']]);
        
        $this->middleware('permission:report-mailer-lead-delete', ['only' => [ 'deleteMailTracker']]);
    }

    public function activity() {
        $user = auth()->user();

        $isAdminUser = $this->checkingIsAdminUser($user);

        $allDisplay = $this->checkingIsAllAccountDisplay($user,$isAdminUser);

        $agentId = $user->id;
        $agentUsers = $this->getAgentListing($isAdminUser, $agentId, false,['Manager']);
        
        return view('activityreport/tracker.activity',compact("agentUsers","agentId","isAdminUser","allDisplay"));
    }

    public function mailerleadtracker() {
        $user = auth()->user();

        $isAdminUser = $this->checkingIsAdminUser($user);

        $allDisplay = $this->checkingIsAllAccountDisplay($user,$isAdminUser);

        $agentId = $user->id;
        $agentEmail = $user->email;
        $agentUsers = $this->getAgentListing($isAdminUser,$agentId,false,['Manager']);
        $leadSource = LeadSource::select('id','name')->where('status', 1)->get();
        $contactsTitle = Lead::contactTitle();
        $statusOptions = self::getContactStatusOptions();

        $edit = false;

        // echo "<pre>";print_r($statusOptions);exit;
        return view('activityreport/tracker.mailerleadtracker',compact("leadSource","agentUsers","contactsTitle","agentId","edit",'statusOptions',"isAdminUser","allDisplay"));
    }

    public function editMailTracker($encoded_id)
    {
        $id = base64_decode($encoded_id);
        // echo $id;exit;
        $mailLead = MailerLeadTracker::where("id",$id)->first();
        if(!$mailLead){
            return redirect()->route('agentreport.mailerLeadIndex');
        }
        $user = auth()->user();

        $isAdminUser = $this->checkingIsAdminUser($user);

        $allDisplay = $this->checkingIsAllAccountDisplay($user,$isAdminUser);

        $agentId = $user->id;
        $agentEmail = $user->email;
        $agentUsers = $this->getAgentListing($isAdminUser,$agentId,false,['Manager']);
        $leadSource = LeadSource::select('id','name')->where('status', 1)->get();
        $contactsTitle = Lead::contactTitle();
        $statusOptions = self::getContactStatusOptions();

        $edit = true;

        // echo "<pre>";print_r($statusOptions);exit;
        return view('activityreport/tracker.mailerleadtracker',compact("leadSource","agentUsers","contactsTitle","agentId",'statusOptions',"edit",'mailLead',"isAdminUser","allDisplay"));
    }

    public function deleteMailTracker($encoded_id)
    {
        $id = base64_decode($encoded_id);
        // echo $id;exit;
        $mailLead = MailerLeadTracker::where("id",$id)->first();
        if($mailLead){
            $mailLead->delete();
        }
        return response()->json([
            'status' => true,
            'message' => "Record deleted successfully"
        ]);
    }

    public function saveAgentActivity(Request $request)
    {
        $data = $request->except('signed_aor_doc', 'aor');
        $user = auth()->user();

        if(ActivityReport::where("date",$data['date'])->where("user_id",$data['user_id'])->first()){
            return response()->json([
                'status' => false,
                'redirection' => false,
                'message' => 'You cannot fill out the Agent Activity Form twice in a single day.'
            ]);
        }  
        // echo "<pre>"
        $report = ActivityReport::create($data);

        if ($request->has('aor') && is_array($request->aor)) {
            $ins = [];
            foreach ($request->aor as $aorData) {
                $ins[] = [
                    'activity_report_id' => $report->id,
                    'aor' => $aorData['aor'] ?? null,
                    'aor_community_name' => $aorData['aor_community_name'] ?? null,
                    'aor_effective_date' => $aorData['aor_effective_date'] ?? null,
                    'expiring_aor_premium' => $aorData['expiring_aor_premium'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if(count($ins) > 0){
                ActivityReportAor::insert($ins);
            }
        }

        if ($request->hasFile('signed_aor_doc')) {
            foreach ($request->file('signed_aor_doc') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = time();

                $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName); 
                $fileName = $safeName . $timestamp . '.' . $extension;

                $relativePath = $file->storeAs('uploads/signed_docs', $fileName, 'public');
                $publicUrl = 'storage/' . $relativePath;

                ActivityReportFile::create([
                    'activity_report_id' => $report->id,
                    'file_path' => $publicUrl,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }
        }

        $redirection = false;
        if($user->can('report-activity-result')){
            $redirection = true;
        }

        // toastr()->success("Agent activity Report saved successfully.");

        return response()->json([
            'status' => true,
            'redirection' => $redirection,
            'message' => 'Agent activity report saved successfully.'
        ]);
    }

    public function saveMailLeadTracker(Request $request)
    {
        $msg = [
            "error" => false,
            "msg" => "",
        ];
        $rules = [
            'mailer_id' => 'nullable|exists:mailer_leads_tracker,id',
            'business_address' => 'nullable|string|max:191|regex:/^\d.*/',
            'contact_address' => 'nullable|string|max:191|regex:/^\d.*/',
        ];
        $niceNames = [
            'mailer_id' => 'Mailer ID',
            'business_address' => 'Business Address',
            'contact_address' => 'Contact Address'
        ];
        $validator = Validator::make($request->all(), $rules, [], $niceNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $data = $request->input();
        $user = auth()->user();

        $data['status'] = 0;

        // echo "<pre>";print_r($data);exit;

        if (empty($data['mailer_id'])) {
            $report = MailerLeadTracker::create($data);
            $msg['msg'] = "Mailer Lead Tracker saved successfully.";
        } else {
            $report = MailerLeadTracker::findOrFail($data['mailer_id']);
            $report->update($data);
            $msg['msg'] = "Mailer Lead Tracker updated successfully.";
        }
        $tracker_id = $report->id;

        if(empty($report->lead_id)){
            $checkEntry = $this->checkCanMakeEntryLead($data);

            if($checkEntry){
                $msg = $this->createLeadWithMailerData($data,$tracker_id);
            }
        }

        if($msg['error']){
            MailerLeadTracker::where("id", $tracker_id)->update([
                "status" => 2
            ]);
        }

        // if($msg['error']){
        //     toastr()->error($msg['msg']);
        // }
        // else{
        //     toastr()->success($msg['msg']);
        // }

        $redirection = false;
        if($user->can('report-mailer-lead-result')){
            $redirection = true;
        }

        return response()->json([
            'status' => !$msg['error'],
            'redirection' => $redirection,
            'message' => $msg['msg']
        ]);
    }

    private function checkCanMakeEntryLead($data)
    {
        $requiredFields = [
            'business',
            'business_type',
            'business_address',
            'business_city',
            'business_zip',
            'contact_firstname',
            'contact_lastname',
            'contact_address',
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    private function createLeadWithMailerData($data, $tracker_id)
    {
        $response = [
            'error' => false,
            'msg' => '',
        ];
        try {
            DB::beginTransaction();
            // Get latitude & longitude from address
            $addressParts = array_filter([
                $data['business_address'] ?? '',
                $data['business_city'] ?? '',
                $data['business_zip'] ?? '',
            ]);
            $new_address = implode(', ', $addressParts);
            $lat_long = parent::getLatLngFromGoogle($new_address);
            $lat = $lat_long['lat'] ?? null;
            $long = $lat_long['long'] ?? null;

            // Generate lead slug & check duplication
            $lead_slug = $this->generateSlug([
                $data['business_type'],
                $data['business'],
                $data['business_city'],
                $data['business_zip'],
            ]);
            $name = $this->removeSpecialCharacters($data['business']);
            $slugExistance = $this->checkLeadSlugExistanceWithDistance($lead_slug, $lat, $long);

            if (!empty($slugExistance['existanceCount'])) {
                throw new \Exception(implode('</br>', $slugExistance['message']));
            }

            // $leadSourceName = LeadSource::where('id', $data['lead_source'] ?? null)
            // ->value('name') ?? '';

            // Create Lead
            $lead = Lead::create([
                'type'        => $data['business_type'],
                'name'        => $name,
                'address1'    => $data['business_address'],
                'city'        => $data['business_city'],
                'zip'         => $data['business_zip'],
                'latitude'    => $lat,
                'longitude'   => $long,
                'lead_slug'   => $lead_slug,
                'lead_source' => $data['lead_source'],
            ]);

            create_log($lead, 'Create Lead', '');

            // Generate contact slug & check duplication
            $addressWithNumber = preg_match('/\d+/', $data['contact_address'], $matches) ? $matches[0] : $data['contact_address'];
            $contactSlug = $this->generateSlug([
                $data['contact_firstname'],
                $data['contact_lastname'],
                $addressWithNumber
            ]);
            $contactExistance = $this->checkContactSlugExistance($contactSlug);

            if (!empty($contactExistance['existanceCount'])) {
                throw new \Exception(implode('</br>', $contactExistance['message']));
            }

            // Create Contact
            $contact = Contact::create([
                'c_first_name' => $data['contact_firstname'],
                'c_last_name'  => $data['contact_lastname'],
                'c_full_name'  => $data['contact_firstname'] . ' ' . $data['contact_lastname'],
                'c_address1'   => $data['contact_address'],
                'c_title'      => $data['contact_title'],
                'c_phone'      => $data['phone'],
                'c_email'      => $data['email_address'],
                'c_agent_id'   => $data['user_id'],
                'c_status'     => $data['contact_status'],
                'contact_slug' => $contactSlug,
            ]);

            // Associate contact with lead
            $contact->leads()->associate($lead);
            $contact->save();

            create_log($lead, 'Create Contact: ' . $contact->c_full_name, '');

            $this->contactbasedleadstatusupdate($lead->id,$data['user_id'],$data['contact_status']);

            $agent_type_status = ContactStatus::where('id',$data['contact_status'])->first();
            if($agent_type_status){
                $own_status = $agent_type_status->display_in_pipedrive;
                $this->updateDialingLists($data['contact_status'], $contact->id, $lead->id,$data['user_id'],$own_status);
                $this->setContactToQueue($lead);

                $message = User::where("id",$data['user_id'])->value("name") . ' has updated status of contact : ' . $contact->id . ' to ' . $agent_type_status->name . ' present in lead: ' . $lead->id;
                Agentlog::updateOrCreate(
                    ['user_id' => $data['user_id'], 'contact_id' => $contact->id],
                    ['message' => $message, 'user_id' => $data['user_id'], 'lead_id' => $lead->id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
                );
            }

            if(!empty($data['status_note'])){
                // Create Note
                $note = new Note([
                    'title'       => "Initial Mailer Form Mail Note",
                    'description' => $data['status_note'],
                ]);
                $note->leads()->associate($lead);
                $note->contacts()->associate($contact);
                $note->save();

                create_log($lead, 'Create Note: ' . $note->title, '');
            }

            // Update tracker status
            MailerLeadTracker::where("id", $tracker_id)->update([
                "status" => 1,
                "lead_id" => $lead->id
            ]);

            DB::commit();
            $response['msg'] = "Lead created successfully with the given Information";
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['msg'] = $e->getMessage();
            DB::rollBack();
        }

        return $response;
    }

    public function activityReport() {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);

        $allDisplay = $this->checkingIsAllAccountDisplay($user,$isAdminUser);

        $agentId = auth()->user()->id;
        $agentEmail = auth()->user()->email;
        $agentUsers = $this->getAgentListing($isAdminUser,$agentId,false,['Manager']);

        // echo "<pre>";print_r($agentUsers);exit;

        
        return view('activityreport/report.activity-report',compact("agentUsers","agentId","isAdminUser","allDisplay"));
    }

    public function activityList(Request $request)
    {
        // echo "<pre>";print_r($request->input());exit;
        $user = auth()->user();
        $manager_id = $this->getManagerId($user);
        $query = $this->activityListQuery($request->input(),false,$manager_id);

        if ($request->view_type == 1) {
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('agent_name', function ($row) {
                    return optional($row->agent)->name ?? '';
                })
                ->addColumn('details', function ($row) {
                    $id = base64_encode($row->id);
                    $url = url("/agentreport/activity_details/{$id}");
                    return '<a href="'.$url.'" class="btn btn-sm btn-info">View</a>';
                })
                ->rawColumns(['details'])
                ->make(true);
        }

        // Consolidated view
        return datatables()->of($query->get())
            ->addIndexColumn()
            // ->editColumn('total_expiry_policies_premium', function ($row) {
            //     $value = (float) ($row->total_expiry_policies_premium ?? 0);
            //     return number_format($value, 2);
            // })
            ->make(true);
    }

    public function activityListDownload(Request $request)
    {
        $requestData = $request->all();

        $mail_agent_id = env('MY_MAIL_SENT_USER_ID');

        $smtp_setup_data = $this->checkMailConfigurationUserWise($mail_agent_id);
        if($smtp_setup_data == 0){
            return response()->json([
                "status" => false,
                'message' => 'You do not have SMTP configuration. Please set it up before attempting to download.'
            ]);
        }

        $user = auth()->user();
        $manager_id = $this->getManagerId($user);

        $requestData["manager_id"] = $manager_id;

        ActivityReportDownload::dispatch( $requestData,$mail_agent_id);

        return response()->json([
            "status" => true,
            'message' => 'Report is being generated. You will receive it via email shortly.'
        ]);
    }

    public function activityDetails($encoded_id)
    {
        $id = base64_decode($encoded_id);

        $report = ActivityReport::find($id);
        $aor = $report->aor()->select('aor','aor_community_name','aor_effective_date','expiring_aor_premium')->get();
        $files = $report->files()->select('id','file_path','original_name','mime_type')->get();

        if(!$report){
            return redirect()->route('agentreport.activityReport');
        }

        return view('activityreport/report.activity-details-report',compact("report","aor","files"));
    }

    public function mailerLeadReport() {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);

        $allDisplay = $this->checkingIsAllAccountDisplay($user,$isAdminUser);

        $agentId = auth()->user()->id;
        $agentEmail = auth()->user()->email;
        $agentUsers = $this->getAgentListing($isAdminUser,$agentId,false,['Manager']);
        $leadSource = LeadSource::select('id','name')->where('status', 1)->get();

        // echo "<pre>";print_r($agentUsers);exit;

        
        return view('activityreport/report.mail-tracker-report',compact("agentUsers","agentId","leadSource","isAdminUser","allDisplay"));
    }

    public function mailLeadTrackerList(Request $request)
    {
        $user = auth()->user();
        $manager_id = $this->getManagerId($user);

        $query = $this->generateMailLeadTrackerData($request->all(),$manager_id);

        if ($request->view_type == 1) {
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('agent_name', function ($lead) {
                    return optional($lead->agent)->name ?? '-';
                })
                ->addColumn('lead_source_name', function ($lead) {
                    return optional($lead->leadSource)->name ?? '-';
                })
                ->addColumn('business', function ($lead) {
                    return $lead->business ?? '-';
                })
                ->addColumn('contact_firstname', function ($lead) {
                    return $lead->contact_firstname ?? '-';
                })
                ->addColumn('contact_lastname', function ($lead) {
                    return $lead->contact_lastname ?? '-';
                })
                ->addColumn('phone', function ($lead) {
                    return $lead->phone ?? '-';
                })
                ->addColumn('email_address', function ($lead) {
                    return $lead->email_address ?? '-';
                })
                ->addColumn('status_note', function ($lead) {
                    return $lead->status_note ?? '-';
                })
                // ->addColumn('date', function ($lead) {
                //     return $lead->date ? \Carbon\Carbon::parse($lead->date)->format('m/d/Y') : 'N/A';
                // })
                ->addColumn('action', function ($lead) {
                    $id = base64_encode($lead->id);
                    $view = '';
                    if ($lead->status == 0) {
                        $view = '<a href="/agentreport/mailerleadtracker/edit/'.$id.'" class="btn btn-sm btn-success"><i class="fa fa-edit"></i></a>';
                    } elseif ($lead->status == 1) {
                        $view = '<a href="/leads/edit/'.base64_encode($lead->lead_id).'" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
                    } elseif ($lead->status == 2) {
                        $view = '<button class="btn btn-sm btn-danger deleteMailer" data-id="'.$id.'"><i class="fa fa-trash"></i></button>';
                    }
                    return $view;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Consolidated View
        return datatables()->of($query->get())
            ->addIndexColumn()
            ->make(true);
    }
  

    public function mailLeadTrackerListDownload(Request $request)
    {
        $requestData = $request->all();

        $mail_agent_id = env('MY_MAIL_SENT_USER_ID');
        $smtp_setup_data = $this->checkMailConfigurationUserWise($mail_agent_id);
        if($smtp_setup_data == 0){
            return response()->json([
                "status" => false,
                'message' => 'You do not have SMTP configuration. Please set it up before attempting to download.'
            ]);
        }

        $user = auth()->user();
        $manager_id = $this->getManagerId($user);

        $requestData["manager_id"] = $manager_id;

        ProcessMailerLeadTrackerReportJob::dispatch( $requestData,$mail_agent_id);

        return response()->json([
            "status" => true,
            'message' => 'Report is being generated. You will receive it via email shortly.'
        ]);

    }

    public function fileDownload($id)
    {
        $file = ActivityReportFile::find($id);

        if (!$file || !$file->file_path) {
            abort(404, 'File path missing.');
        }

        // Clean the DB path by removing leading /storage/
        $cleanPath = preg_replace('#^/?(storage|app/public)/#', '', $file->file_path);

        // Build full path
        $absolutePath = storage_path('app/public/' . $cleanPath);

        // echo $absolutePath;exit;

        if (!file_exists($absolutePath)) {
            abort(404, 'File does not exist at ' . $absolutePath);
        }

        return response()->download($absolutePath);
    }         

    public function daillyCallReport() {
        $user = auth()->user();
        $isAdminUser = $this->checkingIsAdminUser($user);

        $allDisplay = $this->checkingIsAllAccountDisplay($user,$isAdminUser);

        $agentId = auth()->user()->id;
        $agentEmail = auth()->user()->email;
        $agentUsers = $this->getAgentListing($isAdminUser,$agentId,false,['Manager']);

        // echo "<pre>";print_r($agentUsers);exit;

        
        return view('activityreport/report.daillyCallReport',compact("agentUsers","agentId","isAdminUser","allDisplay"));
    }

    public function dailycallReportList(Request $request)
    {
        $formatted_date = $this->getFormatedDate($request->input());

        $agent_id = $request->agent;

        $user = auth()->user();
        $manager_id = $this->getManagerId($user);

        $results =  $this->generateDailyReportData($agent_id,$formatted_date["from"],$formatted_date["to"],$manager_id);

        // return response()->json(['data' => $results]);
        return datatables()->of($results)
        ->addIndexColumn()
        ->make(true);
    }

    public function dailycallReportListDownload(Request $request)
    {
        $requestData = $request->all();

        $mail_agent_id = env('MY_MAIL_SENT_USER_ID');

        $smtp_setup_data = $this->checkMailConfigurationUserWise($mail_agent_id);
        if($smtp_setup_data == 0){
            return response()->json([
                "status" => false,
                'message' => 'You do not have SMTP configuration. Please set it up before attempting to download.'
            ]);
        }

        $user = auth()->user();
        $manager_id = $this->getManagerId($user);

        $requestData["manager_id"] = $manager_id;

        ProcessMailDailyCallReportJob::dispatch( $requestData,$mail_agent_id);

        return response()->json([
            "status" => true,
            'message' => 'Report is being generated. You will receive it via email shortly.'
        ]);

    }

    public function testUrlForBigOCean()
    {
        $client = Client::account('default');

        try {
            $client->connect();
        } catch (\Exception $e) {
            logger()->error('IMAP connection error: ' . $e->getMessage());
            return;
        }

        $folder = $client->getFolder('INBOX');

        $mailSubjectText = Setting::where('id', 1)->value('mail_fetching_subject');

        $mailSubjectText = !empty($mailSubjectText)?$mailSubjectText:'Google sheet link';

        $messages = $folder->query()
            // ->unseen() // unread emails only
            ->since(now()->subDays(10))
            ->limit(10)
            ->subject($mailSubjectText)
            ->get();

        $messages = $messages->sortByDesc(function ($msg) {
            return optional($msg->getDate()->first())->getTimestamp();
        });

        echo "<pre>";print_r($messages);exit;
    }

    public function searchComm(Request $request)
    {
        $keyword = $request->get('keyword');
        $leads = Lead::select('name', 'address1', 'id','city','state','zip')->where('name', 'like', "%{$keyword}%")->limit(10)->get();

        return response()->json($leads);
    }

}
