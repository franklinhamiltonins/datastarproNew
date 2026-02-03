<?php

namespace App\Http\Controllers\pipedrive;

use App\Http\Controllers\Leads\ContactController;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Model\User;
use DB;
use Hash;
use App\Model\ContactStatus;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Log;
use App\Model\File;
use App\Model\Setting;
use App\Model\EventLogs;
use App\Model\LeadsModel\Note;
use App\Model\Agentlog;
use App\Model\LeadSource;
use App\Model\InsuranceType;
use App\Model\LeadAsanaDetail;
use App\Model\AsanaQuestionDetail;
use App\Model\AsanaQuestion;
use App\Model\LeadInfoLog;
use App\Model\LeadAdditionalPolicy;
use App\Model\Carrier;
use App\Model\Rating;
use App\Model\Message;
use App\Model\Template;
use App\Model\UserTemplate;
use App\Model\SmtpConfiguration;

use App\Traits\SMTPRelatedTrait;
use App\Traits\CommonFunctionsTrait;
use App\Traits\LoginFunctionTrait;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class PipedriveLoginController extends ContactController
{
    use CommonFunctionsTrait,SMTPRelatedTrait,LoginFunctionTrait;
    public function logout()
    {
        if (auth()->check()) {
            Auth::logout();
        }
        return response()->json([
            'status' => true,
            'message' => 'Logout Successfully',
        ],200);
    }

    public function checkAlreadyLogin()
    {
        // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'auth not found',
                'isLoggedIn' => false,
                'credentials' => null,
            ], 400);
        }

        // Get the authenticated user
        $user = auth()->user();
        $roles = $user->getRoleNames()->toArray();
        // return $roles;
        $credentials = $user;
        $agentWisePermission = $this->getAgentWisePermission($user);

        $isAdminUser = $agentWisePermission["isAdminUser"];

        // Initialize agent-related variables
        $agentId = $isAdminUser ? 0 : $user->id;
        $agentUsers = $this->getAgentListing($isAdminUser,$agentId,true,$roles);

        // Return a successful response
        return response()->json([
            'status' => true,
            'message' => 'auth found',
            'isLoggedIn' => true,
            'credentials' => $credentials,
            'agent_id' => $agentId,
            'agent_users' => $agentUsers,
            'agentWisePermission' => $agentWisePermission,
        ], 200);
    }

    public function request_login(Request $request)
    {
        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Return an error if the user does not exist
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Email',
                'isLoggedIn' => false,
                'credentials' => null,
            ], 200);
        }

        // Check if the provided password matches the user's stored password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Password',
                'isLoggedIn' => false,
                'credentials' => null,
            ], 200);
        }

        if($this->loginNotification($user)){
            return response()->json([
                'status' => true,
                'message' => '',
                'userId' => $user->id,
                'showOtpBox' => true,
            ], 200);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Something Went Wrong',
                'userId' => 0,
                'showOtpBox' => false,
            ], 200);
        }
    }

    public function request_verify(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        if($latestOtp == $request->otp){
            $user = User::where('id', $request->user_id)->first();
            $this->verifyMarkUserOtp($request->user_id,$latestOtp);
            Auth::login($user);
            $credentials = auth()->user();
            $agentWisePermission = $this->getAgentWisePermission($user);

            $isAdminUser = $agentWisePermission["isAdminUser"];

            // Initialize agent-related variables
            $agentId = $isAdminUser ? 0 : $user->id;

            $agentUsers = $this->getAgentListing($isAdminUser,$agentId);

            return response()->json([
                'status' => true,
                'redirectTo' => "",
                'message' => '',
                'totalAttempt' => 5,
                'triedAttempt' => 0,
                'isLoggedIn' => true,
                'credentials' => $credentials,
                'agent_id' => $agentId,
                'agent_users' => $agentUsers,
                'agentWisePermission' => $agentWisePermission,
            ], 200);
        }
        else{
            $this->invalidUserAttempt($request->user_id,$latestOtp);
            return response()->json([
                'status' => false,
                'redirectTo' => 'DONE 2',
                "message" => "Incorrect Otp",
                'totalAttempt' => 5,
                'triedAttempt' => $this->getUserTriedAttempt($request->user_id,$latestOtp),
                'isLoggedIn' => false,
            ], 200);
        }
    }

    public function resendOtp(Request $request)
    {
        $latestOtp = $this->getUserLatetOtp($request->user_id);

        $recipientEmail = User::where('id', $request->user_id)->value('email') ?? '';
        $recipientName = User::where('id', $request->user_id)->value('name') ?? '';

        $this->send2FAMail($latestOtp,$recipientEmail,$recipientName);

        return response()->json([
                'status' => true,
                'message' => 'DONE 3',
                'userId' => 0,
                'showOtpBox' => true,
            ], 200);
    }

    public function leadfiledownload($agentId = 0,$name="")
    {
        $filename = "leads_export.csv";
        $statusList = ContactStatus::select('id','name')
            ->where('false_status', 0)
            ->where('display_in_pipedrive', 1)
            ->orderBy('priority', 'ASC')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($statusList,$agentId,$name) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Lead ID', 'Lead Name', 'Address', 'Total Premium', 'Policy Renewal Date',
                'Pipeline Agent ID', 'Agent Name', 'Agent Email','Pipeline Status ID',  'Pipeline Status'
            ]);

            foreach ($statusList as $status) {
                $list = Lead::where('pipeline_status_id', $status->id)
                    ->join('users', 'leads.pipeline_agent_id', '=', 'users.id');

                // Correct filtering by agent ID
                if (!empty($agentId)) {
                    $list = $list->where('pipeline_agent_id', $agentId);
                }

                if (!empty($name)) {
                    $list = $list->where('leads.name',"like", "%".$name."%");
                }

                // Correctly chain select and chunk
                $list->select(
                        'leads.id',
                        'leads.name',
                        'leads.address1',
                        'leads.total_premium',
                        'leads.policy_renewal_date',
                        'leads.pipeline_agent_id',
                        'users.name as agent_name',
                        'users.email as agent_email',
                        'leads.pipeline_status_id',
                    )
                    ->chunk(1000, function ($leads) use ($handle, $status) {
                        foreach ($leads as $lead) {
                            fputcsv($handle, [
                                $lead->id,
                                $lead->name,
                                $lead->address1,
                                $lead->total_premium,
                                $lead->policy_renewal_date,
                                $lead->pipeline_agent_id,
                                $lead->agent_name,
                                $lead->agent_email,
                                $lead->pipeline_status_id,
                                $status->name
                            ]);
                        }
                    });
            }


            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function leadasanafiledownload($agentId = 0,$name="")
    {
        // echo $agentId."  ----  ".$name;exit;
        $filename = "leads_bindmgmt_export.csv";
        $questions = AsanaQuestion::select('id', 'name')->where("status",1)->orderBy("priority","ASC")->get();

        $status = ContactStatus::select('id', 'name')
                    ->where('special_marker', 3)
                    ->first();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($questions, $status, $agentId,$name) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Lead ID', 'Lead Name', 'Address', 'Total Premium', 'Policy Renewal Date',
                'Pipeline Agent ID', 'Agent Name', 'Agent Email', 'Pipeline Status ID', 'Pipeline Status', 'Asana Stage'
            ]);

            foreach ($questions as $key => $question) {
                if ($key === 0) {
                    $leadList = Lead::leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                        ->where(function ($query) {
                            $query->where('lead_asana_details.asana_stage', 1)
                                  ->orWhereNull('lead_asana_details.lead_id');
                        })
                        ->where('leads.pipeline_status_id', $status->id ?? 0)
                        ->join('users', 'leads.pipeline_agent_id', '=', 'users.id');

                    
                } else {
                    $leadList = LeadAsanaDetail::join('leads', 'lead_asana_details.lead_id', '=', 'leads.id')
                        ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
                        ->where('lead_asana_details.asana_stage', $question->id)
                        ->where('lead_asana_details.stage_completed', 0)
                        ->whereNull('leads.deleted_at');
                }
                if (!empty($agentId)) {
                    $leadList = $leadList->where('leads.pipeline_agent_id', $agentId);
                }

                if (!empty($name)) {
                    $leadList = $leadList->where('leads.name',"like", "%".$name."%");
                }

                $leadList->select(
                        'leads.id',
                        'leads.name',
                        'leads.address1',
                        'leads.total_premium',
                        'leads.policy_renewal_date',
                        'leads.pipeline_agent_id',
                        'users.name as agent_name',
                        'users.email as agent_email',
                        'leads.pipeline_status_id',
                    )
                    ->chunk(1000, function ($leads) use ($handle, $status, $question) {
                        foreach ($leads as $lead) {
                            fputcsv($handle, [
                                $lead->id,
                                $lead->name,
                                $lead->address1,
                                $lead->total_premium,
                                $lead->policy_renewal_date,
                                $lead->pipeline_agent_id,
                                $lead->agent_name,
                                $lead->agent_email,
                                $lead->pipeline_status_id,
                                $status->name ?? '',
                                $question->name
                            ]);
                        }
                    });
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function differentDealStatus()
    {
        $status = ContactStatus::select('id','name')->where('false_status',0)->where('display_in_pipedrive',1)->orderBy('priority','ASC')->get();

        return response()->json([
            'status' => true,
            'message' => 'status',
            'status_list' =>  $status,
        ],200);
    }

    public function leadiDBasedData_OLD($leadId)
    {
        $leadQuery = Lead::where('id', $leadId)
        ->with(['contacts' => function ($query) {
            $query->orderBy('c_status', 'desc'); // Sorting contacts by c_status in descending order
            $query->with('contactStatus:id,name');
        }])
        ->first();

        $pipeline_status_name = '';
        $special_marker = null;
        $pipeline_agent_name = '';


        $users = User::select('name')->where('id',$leadQuery->pipeline_agent_id)->first();
        if($users){
            $pipeline_agent_name = $users->name;
        }
        unset($users);

        $status = ContactStatus::select('special_marker','name')->where('id',$leadQuery->pipeline_status_id)->first();
        if($status){
            $special_marker = $status->special_marker;
            $pipeline_status_name = $status->name;
        }
        unset($status);

        $leadQuery->pipeline_status_name = $pipeline_status_name;
        $leadQuery->special_marker = $special_marker;
        $leadQuery->pipeline_agent_name = $pipeline_agent_name;

        $leadQuery->property_carrier_name = !empty($leadQuery->propertyCarrier->name)?$leadQuery->propertyCarrier->name:'';
        $leadQuery->gl_carrier_name = !empty($leadQuery->glCarrier->name)?$leadQuery->glCarrier->name:'';
        $leadQuery->ci_carrier_name = !empty($leadQuery->ciCarrier->name)?$leadQuery->ciCarrier->name:'';
        $leadQuery->do_carrier_name = !empty($leadQuery->doCarrier->name)?$leadQuery->doCarrier->name:'';
        $leadQuery->u_carrier_name = !empty($leadQuery->umbrellaCarrier->name)?$leadQuery->umbrellaCarrier->name:'';
        $leadQuery->wc_carrier_name = !empty($leadQuery->wcCarrier->name)?$leadQuery->wcCarrier->name:'';
        $leadQuery->f_carrier_name = !empty($leadQuery->floodCarrier->name)?$leadQuery->floodCarrier->name:'';
        $leadQuery->dic_carrier_name = !empty($leadQuery->dcCarrier->name)?$leadQuery->dcCarrier->name:'';
        $leadQuery->xwind_carrier_name = !empty($leadQuery->xwindCarrier->name)?$leadQuery->xwindCarrier->name:'';
        $leadQuery->eb_carrier_name = !empty($leadQuery->ebCarrier->name)?$leadQuery->ebCarrier->name:'';
        $leadQuery->ca_carrier_name = !empty($leadQuery->caCarrier->name)?$leadQuery->caCarrier->name:'';
        $leadQuery->m_carrier_name = !empty($leadQuery->marinaCarrier->name)?$leadQuery->marinaCarrier->name:'';

        $leadQuery->property_rating_name = !empty($leadQuery->propertyRating->name)?$leadQuery->propertyRating->name:'';
        $leadQuery->gl_rating_name = !empty($leadQuery->generaLiablityRating->name)?$leadQuery->generaLiablityRating->name:'';
        $leadQuery->ci_rating_name = !empty($leadQuery->crimeInsuranceRating->name)?$leadQuery->crimeInsuranceRating->name:'';
        $leadQuery->do_rating_name = !empty($leadQuery->directorOfficerRating->name)?$leadQuery->directorOfficerRating->name:'';
        $leadQuery->u_rating_name = !empty($leadQuery->uRating->name)?$leadQuery->uRating->name:'';
        $leadQuery->wc_rating_name = !empty($leadQuery->workerCompansestionRating->name)?$leadQuery->workerCompansestionRating->name:'';
        $leadQuery->f_rating_name = !empty($leadQuery->fRating->name)?$leadQuery->fRating->name:'';

        $sql_query = "SELECT cs.id, cs.name, SUM( COALESCE( CASE WHEN TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp)) < 1 THEN 1 ELSE TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp)) END, 0) ) AS minutes FROM contact_status cs LEFT JOIN lead_status_wise_log lswl ON cs.id = lswl.status_id AND lswl.lead_id = ".$leadId." WHERE cs.display_in_pipedrive IS NOT NULL GROUP BY cs.id ORDER BY cs.priority ASC"; 

        // $sql_query = "SELECT cs.id, cs.name, SUM(COALESCE(TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp)), 0)) AS minutes from contact_status cs LEFT JOIN lead_status_wise_log lswl ON cs.id = lswl.status_id AND  lswl.lead_id = ".$leadId." where display_in_pipedrive is not null GROUP BY cs.id ORDER BY  cs.priority ASC";

        $status_list = DB::select($sql_query);

        foreach ($status_list as $key => $valuestatus) {
            $status_list[$key]->days = ceil($valuestatus->minutes/1440);
        }

        $additonalPolicy = $leadQuery->leadAdditionalpolicy()->get();

        foreach ($additonalPolicy as $key => $policy) {
            $additonalPolicy[$key]->carrier_name = !empty($policy->listCarrier->name)?$policy->listCarrier->name:'';
        }

        return [
            'lead' =>  $leadQuery,
            'status_list' =>  $status_list, 
            'additonalPolicy' =>  $additonalPolicy, 
        ];
    }

    public function leadiDBasedData($leadId)
    {
        $leadQuery = Lead::with([
            'contacts' => function ($query) {
                $query->orderBy('c_status', 'desc')
                    ->with('contactStatus:id,name');

            },
            'propertyCarrier:id,name',
            'glCarrier:id,name',
            'ciCarrier:id,name',
            'doCarrier:id,name',
            'umbrellaCarrier:id,name',
            'wcCarrier:id,name',
            'floodCarrier:id,name',
            'dcCarrier:id,name',
            'xwindCarrier:id,name',
            'ebCarrier:id,name',
            'caCarrier:id,name',
            'marinaCarrier:id,name',
            'propertyRating:id,name',
            'generaLiablityRating:id,name',
            'crimeInsuranceRating:id,name',
            'directorOfficerRating:id,name',
            'uRating:id,name',
            'workerCompansestionRating:id,name',
            'fRating:id,name',
            'leadSource:id,name',
            'leadAdditionalpolicy.listCarrier:id,name',
        ])
        ->where('id', $leadId)
        ->first();

        if (!$leadQuery) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        // Pipeline agent name
        $pipeline_agent_name = optional(
            User::select('name')->find($leadQuery->pipeline_agent_id)
        )->name ?? '';

        // Pipeline status name and special marker
        $status = ContactStatus::select('special_marker', 'name')
            ->find($leadQuery->pipeline_status_id);

        $pipeline_status_name = $status->name ?? '';
        $special_marker = $status->special_marker ?? '';

        // Attach additional fields
        $leadQuery->pipeline_status_name = $pipeline_status_name;
        $leadQuery->special_marker = $special_marker;
        $leadQuery->pipeline_agent_name = $pipeline_agent_name;
        $leadQuery->lead_source_name = $leadQuery->leadSource->name ?? '';
        $leadQuery->lead_source_id = $leadQuery->leadSource->id ?? '';

        // Carrier names
        $leadQuery->property_carrier_name = $leadQuery->propertyCarrier->name ?? '';
        $leadQuery->gl_carrier_name = $leadQuery->glCarrier->name ?? '';
        $leadQuery->ci_carrier_name = $leadQuery->ciCarrier->name ?? '';
        $leadQuery->do_carrier_name = $leadQuery->doCarrier->name ?? '';
        $leadQuery->u_carrier_name = $leadQuery->umbrellaCarrier->name ?? '';
        $leadQuery->wc_carrier_name = $leadQuery->wcCarrier->name ?? '';
        $leadQuery->f_carrier_name = $leadQuery->floodCarrier->name ?? '';
        $leadQuery->dic_carrier_name = $leadQuery->dcCarrier->name ?? '';
        $leadQuery->xwind_carrier_name = $leadQuery->xwindCarrier->name ?? '';
        $leadQuery->eb_carrier_name = $leadQuery->ebCarrier->name ?? '';
        $leadQuery->ca_carrier_name = $leadQuery->caCarrier->name ?? '';
        $leadQuery->m_carrier_name = $leadQuery->marinaCarrier->name ?? '';

        // Rating names
        $leadQuery->property_rating_name = $leadQuery->propertyRating->name ?? '';
        $leadQuery->gl_rating_name = $leadQuery->generaLiablityRating->name ?? '';
        $leadQuery->ci_rating_name = $leadQuery->crimeInsuranceRating->name ?? '';
        $leadQuery->do_rating_name = $leadQuery->directorOfficerRating->name ?? '';
        $leadQuery->u_rating_name = $leadQuery->uRating->name ?? '';
        $leadQuery->wc_rating_name = $leadQuery->workerCompansestionRating->name ?? '';
        $leadQuery->f_rating_name = $leadQuery->fRating->name ?? '';

        // //name issue resolving array
        // $leadQuery->gl_rating = $leadQuery->generaLiablityRating->id ?? '';
        // $leadQuery->ci_rating = $leadQuery->crimeInsuranceRating->id ?? '';
        // $leadQuery->do_rating = $leadQuery->directorOfficerRating->id ?? '';
        // $leadQuery->umbrella_rating = $leadQuery->uRating->id ?? '';
        // $leadQuery->wc_rating = $leadQuery->workerCompansestionRating->id ?? '';
        // $leadQuery->flood_rating = $leadQuery->fRating->id ?? '';

        // Raw SQL for status list
        $sql_query = "
            SELECT 
                cs.id, 
                cs.name, 
                u.name AS agent_name,
                SUM(
                    COALESCE(
                        CASE 
                            WHEN TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp)) < 1 
                            THEN 1 
                            ELSE TIMESTAMPDIFF(MINUTE, lswl.start_timestamp, IF(lswl.end_timestamp IS NULL, NOW(), lswl.end_timestamp)) 
                        END, 0)
                ) AS minutes
            FROM contact_status cs
            LEFT JOIN lead_status_wise_log lswl 
                ON cs.id = lswl.status_id 
                AND lswl.lead_id = ?
            LEFT JOIN users u 
                ON u.id = (
                    SELECT agent_id 
                    FROM lead_status_wise_log lswl2 
                    WHERE lswl2.status_id = cs.id 
                      AND lswl2.lead_id = ? 
                    ORDER BY lswl2.start_timestamp DESC 
                    LIMIT 1
                )
            WHERE cs.display_in_pipedrive IS NOT NULL 
              AND cs.deleted_at IS NULL
            GROUP BY cs.id, cs.name, u.name
            ORDER BY cs.priority ASC
        ";

        $status_list = DB::select($sql_query, [$leadId, $leadId]);


        foreach ($status_list as $key => $valuestatus) {
            $status_list[$key]->days = ceil($valuestatus->minutes / 1440);
        }

        // Add carrier names to additional policy
        $additonalPolicy = $leadQuery->leadAdditionalpolicy;
        foreach ($additonalPolicy as $key => $policy) {
            $policy->carrier_name = $policy->listCarrier->name ?? '';
        }

        return [
            'lead' =>  $leadQuery,
            'status_list' =>  $status_list, 
            'additonalPolicy' =>  $additonalPolicy, 
        ];
    }

    public function individualLeadData(Request $request)
    {
        $lead = Lead::find($request->leadId);
        if(!$lead){
            return response()->json([
                'status' => false,
                'message' => 'status',
                'lead' =>  [],
                'status_list' =>  [],
                'additonalPolicy' =>  [],
            ],200);
        }
        
        $res = $this->leadiDBasedData($request->leadId);
        // echo "<pre>";print_r($res['lead']);exit;
        return response()->json([
            'status' => true,
            'message' => 'status',
            'lead' =>  $res['lead'],
            'status_list' =>  $res['status_list'],
            'additonalPolicy' =>  $res['additonalPolicy'],
        ],200);
    }

    public function leadsNotesList(Request $request)
    {
        $notes = Note::leftjoin('contacts', 'notes.contact_id', '=', 'contacts.id')
        ->leftjoin('users','notes.user_id', '=', 'users.id')
        ->select('notes.id','notes.description','notes.created_at', 'notes.contact_id','contacts.c_full_name as contact_name','users.name as agent_name')
        ->where('notes.lead_id', $request->leadId)
        ->where('notes.deleted_at', null)
        ->orderBy('notes.id', 'desc')->get();

        foreach ($notes as $key => $value) {
            $notes[$key]->created_date = date("m/d/Y", strtotime($value->created_at));

            // echo "<pre>";print_r($notes);exit;
        }

        // echo "<pre>";print_r($notes);exit;

        return response()->json([
            'status' => true,
            'message' => 'notes',
            'notes' =>  $notes,
        ],200);
    }

    public function leadsLogsList(Request $request)
    {
        $agentId = $request->input('agentId');
        $leadId = $request->input('leadId');

        $logsQuery = Log::with('users')
            ->where('lead_id', $leadId)
            ->orderByDesc('id');

        if (!empty($agentId)) {
            $logsQuery->where('user_id', $agentId);
        }

        $logs = $logsQuery->limit(100)->get();

        $logsarray = $logs->map(function ($log) {
            return [
                "username" => $log->users && $log->users->name ? $log->users->name : '',
                "action" => $log->action,
                "id" => $log->id,
                "date" => $log->created_at->format('m/d/Y'),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'logs',
            'logs' => $logsarray,
        ], 200);
    }


    public function leadsFilesList(Request $request)
    {
        $fileQuery = File::query(); //start query
        $leadId = (!empty($request->leadId)) ? $request->leadId : (''); //get the lead id
        
        // $fileQuery->where('lead_id', $leadId);// get the files for the specific lead id
        $fileQuery->whereHasMorph('uploaded_files', [Lead::class], function($query) use($leadId){
            $query->where('uploaded_files_id', $leadId);
        });

        $fileQuery = $fileQuery->select('id','name','description','created_at')->orderBy('created_at','DESC')->get();

        $filesarray = [];

        foreach ($fileQuery as $key => $valuefiles) {
            $filesarray[] = [
                'id' => $valuefiles->id,
                'name' => $valuefiles->name,
                'description' => $valuefiles->description,
                "date" => date("m/d/Y", strtotime($valuefiles->created_at)),
                'download_link' => url("leads/edit/file-download/".$valuefiles->id)
            ];
        }
        unset($fileQuery);

        return response()->json([
            'status' => true,
            'message' => 'files',
            'files' =>  $filesarray,
        ],200);
    }

    public function statusWiseLeadList(Request $request)
    {
        $statusId = $request->input('statusId');
        $agentId = $request->input('agentId');
        $pageNumber = $request->input('page_number', 1);
        $pageSize = $request->input('page_size', 10);

        // Base query for leads
        // $leadQuery = Lead::where('leads.pipeline_status_id', $statusId)
        //     ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
        //     ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
        //     ->leftJoinSub($latestStatus, 'latest_log', function ($join) {
        //         $join->on('leads.id', '=', 'latest_log.lead_id');
        //     })
            // ->leftJoin('lead_status_wise_log as lswl', function ($join) {
            //     $join->on('leads.id', '=', 'lswl.lead_id')
            //         ->where('lswl.status_id', '=', 8);
            // })
        //     ->where(function ($query) {
        //         $query->whereNull('lead_asana_details.lead_id')
        //             ->orWhere('lead_asana_details.stage_completed', '!=', 1);
        //     });

        // if (!empty($agentId)) {
        //     $leadQuery->where(function ($query) use ($agentId) {
        //         $query->where('leads.pipeline_agent_id', $agentId)
        //             ->orWhere('leads.assigned_user_id', $agentId);
        //     });
        // }

        // if (!empty($request->input('searchName'))) {
        //     $leadQuery = $leadQuery->where('leads.name', "like", "%" . $request->input('searchName') . "%");
        // }

        // // Calculate total insured amount
        // $totalInsuredAmount = $leadQuery->sum('total_premium');

        // // Sorting
        // if (!empty($request->input('sortBy'))) {
        //     if ($request->input('sortBy') == 'name') {
        //         $leadQuery = $leadQuery->orderBy('leads.name', $request->input('orderBy', 'asc'));
        //     }
        //     if ($request->input('sortBy') == 'date') {
        //         $leadQuery = $leadQuery->orderBy('status8_start_timestamp', $request->input('orderBy', 'asc'));
        //     }
        //     if ($request->input('sortBy') == 'updated') {
        //         $leadQuery = $leadQuery->orderBy('leads.updated_at', $request->input('orderBy', 'asc'));
        //     }
        //     if ($request->input('sortBy') == 'premium') {
        //         $leadQuery = $leadQuery->orderBy('leads.total_premium', $request->input('orderBy', 'asc'));
        //     }
        // }

        // $selectFields = [
        //     'leads.id',
        //     'leads.name',
        //     'leads.address1',
        //     'leads.created_at',
        //     'leads.updated_at',
        //     'leads.total_premium as insured_amount',
        //     'leads.policy_renewal_date',
        //     'users.name as pipeline_agent_name',
        //     'users.email as pipeline_agent_email',
        //     'leads.pipeline_agent_id',
        //     'leads.assigned_user_id',
        //     'lswl.start_timestamp as status8_start_timestamp',
        // ];

        // Paginate and select relevant fields
        // $leadList = $leadQuery->select($selectFields)
        //     ->paginate($pageSize, ['*'], 'page', $pageNumber);

        // $leadList->getCollection()->load([
        //     'collaborators:id,name,email',
        //     'assignedUser:id,name,email'
        // ]);

        $latestStatus = DB::table('lead_status_wise_log')
        ->select(DB::raw('MAX(id) as id'), 'lead_id')
        ->where('status_id', 8)
        ->groupBy('lead_id');


        $leadQuery = Lead::where('leads.pipeline_status_id', $statusId)
        ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')

        ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')

        ->leftJoinSub($latestStatus, 'latest_log', function ($join) {
            $join->on('leads.id', '=', 'latest_log.lead_id');
        })

        ->leftJoin('lead_status_wise_log as lswl', 'lswl.id', '=', 'latest_log.id')
        ->where(function ($query) {
            $query->whereNull('lead_asana_details.lead_id')
                  ->orWhere('lead_asana_details.stage_completed', '!=', 1);
        });


        if (!empty($agentId)) {
            $leadQuery->where(function ($query) use ($agentId) {
                $query->where('leads.pipeline_agent_id', $agentId)
                      ->orWhere('leads.assigned_user_id', $agentId);
            });
        }

        if (!empty($request->input('searchName'))) {
            $leadQuery->where('leads.name', 'like', '%' . $request->input('searchName') . '%');
        }

        $totalInsuredAmount = $leadQuery->sum('leads.total_premium');

        if ($request->filled('sortBy')) {
            $sortBy = $request->input('sortBy');
            $orderBy = $request->input('orderBy', 'asc');

            switch ($sortBy) {
                case 'name':
                    $leadQuery->orderBy('leads.name', $orderBy);
                    break;

                case 'date':
                    $leadQuery->orderBy('lswl.start_timestamp', $orderBy);
                    break;

                case 'updated':
                    $leadQuery->orderBy('leads.updated_at', $orderBy);
                    break;

                case 'premium':
                    $leadQuery->orderBy('leads.total_premium', $orderBy);
                    break;
            }
        }

        $selectFields = [
            'leads.id',
            'leads.name',
            'leads.address1',
            'leads.created_at',
            'leads.updated_at',
            'leads.total_premium as insured_amount',
            'leads.policy_renewal_date',
            'users.name as pipeline_agent_name',
            'users.email as pipeline_agent_email',
            'leads.pipeline_agent_id',
            'leads.assigned_user_id',

            'lswl.start_timestamp as status8_start_timestamp',
        ];


        $leadList = $leadQuery->select($selectFields)
        ->paginate($pageSize, ['*'], 'page', $pageNumber);

        $leadList->getCollection()->load([
            'collaborators:id,name,email',
            'assignedUser:id,name,email',
        ]);

        $status = ContactStatus::select('special_marker', 'name')->find($statusId);

        if ($status && $status->special_marker === 3) {
            foreach ($leadList->items() as $lead) {
                $lead->display_tile_color = 4; // Green color
                $lead->assigned_user_custom = $lead->customUserGetting();
            }
        } else {
            $notifyDays = Setting::value('process_time_in_day_pipeline') ?? 4;
            $estTime = now()->subDays($notifyDays)->timezone('America/New_York');
            $nowTime = now()->timezone('America/New_York');

            foreach ($leadList->items() as $lead) {
                $displayTileColor = $this->decideColorTile($agentId, $notifyDays, $estTime, $nowTime, $statusId, $lead->id);
                $lead->display_tile_color = $displayTileColor;
                $lead->assigned_user_custom = $lead->customUserGetting();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Lead list fetched successfully',
            'lead_list' => $leadList->getCollection(),
            'pagination' => [
                'total' => $leadList->total(),
                'current_page' => $leadList->currentPage(),
                'last_page' => $leadList->lastPage(),
                'per_page' => $leadList->perPage(),
                'totalInsuredAmount' => number_format($totalInsuredAmount, 2, '.', ''),
            ],
        ], 200);
    }


    public function fetchTotalDealData(Request $request)
    {
        $agentId = $request->input('agentId');

        // Fetch the relevant status list only when needed in the query
        $statusList = $this->pipeDriveDisplayStatusList();

        // Get the sum of premium and count of matched entries
        $result = Lead::whereIn('pipeline_status_id',$statusList);

        if(!empty($agentId)){
            $result = $result->where('pipeline_agent_id',$agentId);
        }
        if (!empty($request->input('searchName'))) {
            $result = $result->where('leads.name',"like", "%".$request->input('searchName')."%");
        }

        $result = $result->selectRaw('SUM(total_premium) as total_insured_amount, COUNT(*) as total_entries')
            ->first();


        $total_insured_amount = "0.00";
        $total_entries = 0;

        if($result){
            $total_insured_amount = !empty($result->total_insured_amount)?number_format($result->total_insured_amount,2):"0.00";
            $total_entries = !empty($result->total_entries)?$result->total_entries:0;
        }

        // Prepare a safe response, even if no result is found
        return response()->json([
            'status' => true,
            'message' => 'Lead total',
            'result' => [
                'total_insured_amount' => $total_insured_amount,
                'total_entries' => $total_entries
            ],
        ], 200);
    }

    public function shiftLeadStatus(Request $request)
    {
        // try {
            // Get the request data
            $leadId = $request->input('leadId');
            $oldStatusId = $request->input('oldStatusId');
            $newStatusId = $request->input('newStatusId');
            $agentId = $request->input('agentId');

            $new_status_name = '';

            if(!empty($request->input('specialType'))){
                $status = ContactStatus::select('id','name')->where('special_marker',$request->input('specialType'))->first();
                if($status){
                    $newStatusId = $status->id;
                    $new_status_name = $status->name;
                }
            }

            $lead  = Lead::where('id', $leadId)
            ->where('pipeline_status_id',$oldStatusId)
            ->first();

            if($lead){
                $lead->pipeline_status_id = $newStatusId;
                $lead->save();

                Contact::where('lead_id',$leadId)->where('c_status', $oldStatusId)
                ->update(['c_status' => $newStatusId]);

                $this->leadstatuslogmakeentry($leadId,$agentId,$newStatusId);
                $checking = $this->statusChangeMsgShoot($lead, $oldStatusId, $newStatusId);

                return response()->json([
                    'status' => true,
                    'message' => 'Contact status updated successfully',
                    'newStatusId' => $newStatusId,
                    'new_status_name' => $new_status_name,
                    'checking' => $checking,
                ], 200);
            }


            // If the update failed, return a failure response
            return response()->json([
                'status' => false,
                'message' => 'Failed to update contact status'
            ], 500);

        // } catch (ModelNotFoundException $e) {
        //     // Catch the 404 exception and return a custom response
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Lead or contact with the given status not found'
        //     ], 404);
        // } catch (\Exception $e) {
        //     // Catch any other exceptions and return a generic error response
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'An unexpected error occurred'
        //     ], 500);
        // }
    }

    public function updateIndividualLead(Request $request)
    {
        $leadId = $request->input('leadId');
        $specialType = $request->input('specialType');
        $agentId = $request->input('agentId');

        $agent_type_status = ContactStatus::select('id','name')->where('special_marker',$request->input('specialType'))->first();
        if($agent_type_status){
            $lead  = Lead::where('id', $leadId)
            ->first();

            if($lead){
                $agent = User::find($agentId);

                if ($agent && $agent->hasRole('Agent')) {
                    
                } else {
                    $agentId = $lead->pipeline_agent_id;
                }
                if($specialType == 3){
                    $contact = Contact::where('lead_id', $leadId)
                    ->where('c_status','!=',8)
                    ->orderBy("c_status","DESC")
                    ->first();

                    if (!$contact) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Failed to update contact status',
                            'newStatusId' => $agent_type_status->id,
                            'new_status_name' => $agent_type_status->name
                        ], 500);
                    }
                    $contact->c_status = $agent_type_status->id;
                    $contact->c_agent_id = $agentId;
                    $contact->save();

                    $contact_id = $contact->id;

                }
                else{
                    $contact = Contact::where('lead_id', $leadId)
                    ->update([
                        'c_status' => $agent_type_status->id,
                        'c_agent_id' => $agentId
                    ]);

                    $lead->pipeline_status_id = $agent_type_status->id;
                    $lead->pipeline_agent_id = $agentId;
                    $lead->save();  

                    $contact_id = 0;

                    $contact = Contact::where('lead_id', $leadId)->first();
                    if($contact){
                        $contact_id = $contact->id;
                    }

                }


                $this->contactbasedleadstatusupdate($leadId,$agentId,$agent_type_status->id);

                $agent_type_status = ContactStatus::where('id',$agent_type_status->id)->first();

                if ($agent_type_status) {
                    // if(empty($agent_type_status->false_status)){
                        $own_status = $agent_type_status->display_in_pipedrive;
                        $this->updateDialingLists($agent_type_status->id, $contact_id, $leadId,$agentId,$own_status);
                        $this->setContactToQueue($lead);

                        $message = User::where("id",$agentId)->value("name") . ' has updated status of contact : ' . $contact_id . ' to ' . $agent_type_status->id . ' present in lead: ' . $leadId;
                        Agentlog::updateOrCreate(
                            ['user_id' => $agentId, 'contact_id' => $contact_id],
                            ['message' => $message, 'user_id' => $agentId, 'lead_id' => $leadId, 'contact_id' => $contact_id, 'status' => 'call_status_updated']
                        );
                    // }
                    // endif;
                }
                create_log($lead, 'Edit Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');

                $res = $this->leadiDBasedData($leadId);

                return response()->json([
                    'status' => true,
                    'message' => 'Contact status updated successfully',
                    'newStatusId' => $agent_type_status->id,
                    'new_status_name' => $agent_type_status->name,
                    'lead' =>  $res['lead'],
                ], 200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'An unexpected error occurred'
        ], 500);
    }

    public function listSpecialStatus(Request $request)
    {
        $specialType = $request->input('specialType');
        $agentId = $request->input('agentId');
        $filterData = !empty($request->input('filterData')) ? json_decode(json_encode($request->input('filterData'))) : (object)[];
        $pageNumber = $request->input('page_number', 1); // Default to page 1 if not provided
        $pageSize = $request->input('page_size', 20);    // Default page size to 20 if not provided

        if(isset($filterData->agent_id)){
            $agentId = $filterData->agent_id;
        }

        if($specialType == 4){
            $status = ContactStatus::select('id', 'name')->where('special_marker', 3)->first();
            if($status){
                $leadQuery = Lead::join('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                ->where(function ($query) use ($status) {
                    $query->where('lead_asana_details.stage_completed', 1);
                          // ->orWhereNull('lead_asana_details.lead_id'); // Ensure there is no entry
                })
                ->where('leads.pipeline_status_id', $status->id);
            }
        }
        else{
            $status = ContactStatus::select('id','name')->where('special_marker',$request->input('specialType'))->first();
            if($status){
                // Build the query
                $leadQuery = Lead::where('leads.pipeline_status_id',$status->id);
            }
        }
        if(!$status){
            return response()->json([
                'status' => false,
                'message' => 'Something Went Wrong',
            ], 500); 
        }

        if(!empty($agentId)){
            $leadQuery = $leadQuery->where('leads.pipeline_agent_id',$agentId);
        }

        if(!empty($filterData)){
            if(!empty($filterData->name)){
                $leadQuery = $leadQuery->where('leads.name', 'like', '%'.$filterData->name.'%');
            }
            // if(!empty($filterData->agent_id)){
            //     $leadQuery = $leadQuery->where('leads.pipeline_agent_id', $filterData->agent_id);
            // }
        }

        // Calculate the total insured amount
        $totalInsuredAmount = $leadQuery->sum('leads.total_premium');

        if(!empty($request->input('columnKey'))){
            $leadQuery = $leadQuery->orderBy($request->input('columnKey'),$request->input('direction'));
        }

        // Select relevant lead fields and paginate the results
        if($specialType == 4){
            $leadList = $leadQuery->select('leads.id', 'leads.name', 'leads.address1','leads.city','leads.zip', 'leads.total_premium as insured_amount','lead_asana_details.renewal_date');
        }else{
            $leadList = $leadQuery->select('leads.id', 'leads.name', 'leads.address1','leads.city','leads.zip', 'leads.total_premium as insured_amount');
        }

        $leadList = $leadQuery->paginate($pageSize, ['*'], 'page', $pageNumber);

        // Return the response
        return response()->json([
            'status' => true,
            'message' => 'Lead list fetched successfully',
            'lead_list' => $leadList->items(), // The actual list of leads
            'pagination' => [
                'total' => $leadList->total(),   // Total number of records
                'current_page' => $leadList->currentPage(), // Current page
                'last_page' => $leadList->lastPage(), // Total number of pages
                'per_page' => $leadList->perPage(), // Number of items per page
                'totalInsuredAmount' => !empty($totalInsuredAmount) ? number_format($totalInsuredAmount, 2) : "0.00"
            ],
        ], 200);
    }

    public function keepEventLog(Request $request)
    {
        $event_id = !empty($request->event_id)?$request->event_id:'';
        if(!empty($event_id)){
            $event = EventLogs::where('event_id',$event_id)->first();
            if(!$event){
                $event = new EventLogs();
                $event->event_id = $event_id;
                $event->status = 1;
            }
            $event->event_name = $request->event_id;
            $event->event_desc = $request->event_desc;
            $event->event_name = $request->event_title;
            $event->event_date = $request->event_date;
            $event->lead_id = $request->leadID;
            $event->agent_id = $request->agentId;
            $event->save();
        }
        return response()->json([
            'status' => false,
            'message' => 'Updated'
        ], 200);

    }

    public function deleteEventLog(Request $request)
    {
        $event_id = !empty($request->event_id)?$request->event_id:'';
        if(!empty($event_id)){
            $event = EventLogs::where('event_id',$event_id)->first();
            if($event){
                $event->delete();
            }
        }
        return response()->json([
            'status' => false,
            'message' => 'Deleted'
        ], 200);

    }

    public function fetchLeadDataEmail(Request $request)
    {
        $lead = Lead::select('pipeline_status_id')->find($request->leadID);

        if (!$lead || !$lead->pipeline_status_id) {
            return response()->json([
                'status' => false,
                'email_list' => []
            ], 200);
        }

        $email_list = Contact::where('lead_id', $request->leadID)
            ->where('c_status', $lead->pipeline_status_id)
            ->whereNotNull('c_email')
            ->pluck('c_email') 
            ->filter()         
            ->values()        
            ->toArray(); 

        return response()->json([
            'status' => !empty($email_list),
            'email_list' => $email_list
        ], 200);
    }

    public function allStatusList(Request $request)
    {
        $status_list = ContactStatus::select('id', 'name', 'false_status', 'display_in_pipedrive')
        ->where(function ($query) {
            $query->whereNull('special_marker')
                  ->orWhere('special_marker', 3);
        })
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'status',
            'status_list' =>  $status_list,
        ],200);
    }

    public function reassignLeadStatus(Request $request)
    {
        $lead  = Lead::where('id', $request->leadId)
        // ->where('pipeline_status_id',$oldStatusId)
        ->first();

        if($lead){
            // $lead->pipeline_status_id = $request->status;
            // $lead->save();
            $agent_id = $request->agent_id;
            $agent = User::find($agent_id);

            if ($agent && $agent->hasRole('Agent')) {
                
            } else {
                $agent_id = $lead->pipeline_agent_id;
            }

            $contact = Contact::where('lead_id', $request->leadId)
                  ->where('id', $request->contact_id)
                  ->first();

            if (!$contact) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update contact status'
                ], 500);
            }
            $contact->c_status = $request->status;
            $contact->c_agent_id = $agent_id;
            $contact->save();

            $this->contactbasedleadstatusupdate($request->leadId,$agent_id,$request->status);

            $agent_type_status = ContactStatus::where('id',$request->status)->first();

            $lead = Lead::find($request->leadId);

            if (!empty($request->status) && $agent_type_status) {
                // if(empty($agent_type_status->false_status)){
                    $own_status = $agent_type_status->display_in_pipedrive;
                    $this->updateDialingLists($request->status, $contact->id, $request->leadId,$agent_id,$own_status);
                    $this->setContactToQueue($lead);

                    $message = User::where("id",$agent_id)->value("name") . ' has updated status of contact : ' . $contact->id . ' to ' . $request->status . ' present in lead: ' . $request->leadId;
                    Agentlog::updateOrCreate(
                        ['user_id' => $agent_id, 'contact_id' => $contact->id],
                        ['message' => $message, 'user_id' => $agent_id, 'lead_id' => $request->leadId, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
                    );
                // }
                // endif;
            }
            create_log($lead, 'Edit Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');

            return response()->json([
                'status' => true,
                'message' => 'Contact status updated successfully'
            ], 200);
        }


        // If the update failed, return a failure response
        return response()->json([
            'status' => false,
            'message' => 'Failed to update contact status'
        ], 500);
    }

    public function addLeadNote(Request $request)
    {
        if(!empty($request->noteId)){
            $note = Note::find($request->noteId);
            if($note){
                $note->contact_id = $request->contact_id;
                $note->description = $request->description;
                $note->save();

                $contact = Contact::find($request->contact_id);

                $note->contacts()->associate($contact);
                $note->save();

                $lead = Lead::find($request->leadId);

                create_log($lead, 'Edit Note : ' . $note->title, '');

                return response()->json([
                    'status' => true,
                    'message' => 'Notes Updated Sucessfully',
                    'note' => !empty($contact->c_full_name)?$contact->c_full_name:'',
                    'is_upadted' => 1
                ], 200);
            }
        }
        else{
            $input = [
                'lead_id' => $request->leadId,
                'contact_id' => $request->contact_id,
                'user_id' => $request->agentId,
                'description' => $request->description,
            ];
            $note = Note::create($input);
            // getting lead and contact
            $lead = Lead::find($request->leadId);
            $contact = Contact::find($request->contact_id);

            //attach the note to lead & contact
            $note->leads()->associate($lead);
            $note->contacts()->associate($contact);
            $note->save();

            create_log($lead, 'Create Note : ' . $note->title, '');

            $newlyaddednote = Note::leftjoin('contacts', 'notes.contact_id', '=', 'contacts.id')
                        ->leftjoin('users','notes.user_id', '=', 'users.id')
                        ->select('notes.id','notes.description','notes.created_at',  'notes.contact_id','contacts.c_full_name as contact_name','users.name as agent_name')
                        ->where('notes.id',$note->id)->first();

            if($newlyaddednote){
                $newlyaddednote->created_date = date("m/d/Y", strtotime($newlyaddednote->created_at));
            }

            return response()->json([
                'status' => true,
                'message' => 'Notes added Sucessfully',
                'note' => $newlyaddednote,
                'is_upadted' => 0
            ], 200);
        }
    }

    public function addLeadFile(Request $request)
    {
        // Validate the request payload
        $request->validate([
            'leadId' => 'required|exists:leads,id',
            'files' => 'required|array',
            'files.*' => 'file',
            'description' => 'nullable|string|max:255',
        ]);

        // Fetch the lead by ID
        $lead = Lead::find($request->leadId);
        if (!$lead) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Lead ID',
            ], 404);
        }

        // Initialize an array to hold saved file data
        $filesArray = [];

        // Check if files are provided
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Generate a unique file name
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Store the file in the public directory
                $filePath = $file->storeAs('uploads', $fileName, 'public');

                // Associate the uploaded file with the lead
                $uploadedFile = $lead->files()->create([
                    'name' => $fileName,
                    'description' => $request->description,
                    'file_path' => '/storage/' . $filePath, // Public storage path
                ]);

                // Log the file upload action
                create_log($lead, 'Upload File: ' . $fileName, '');

                // Add the file details to the array
                $filesArray[] = [
                    'id' => $uploadedFile->id,
                    'name' => $uploadedFile->name,
                    'description' => $uploadedFile->description,
                    'date' => date("m/d/Y", strtotime($uploadedFile->created_at)),
                    'download_link' => url("leads/edit/file-download/" . $uploadedFile->id),
                ];
            }

            // Return success response with saved data
            return response()->json([
                'status' => true,
                'message' => 'Files added successfully',
                'file' => $filesArray,
            ], 200);
        }

        // Return error response if no files are provided
        return response()->json([
            'status' => false,
            'message' => 'No files provided for upload',
        ], 400);
    }

    public function destroyLeadNote(Request $request)
    {
        //get the note to delete
        $note = Note::find($request->id);
        if (!$note) {

            return response()->json([
                'status' => true,
                'message' => 'Note destroyed Sucessfully'
            ], 200);
        }
        $lead = Lead::find($note->leads->id);
        create_log($lead, 'Delete Note : ' . $note->title, '');

        $note->delete();
        return response()->json([
            'status' => true,
            'message' => 'Note destroyed Sucessfully'
        ], 200);
    }

    public function destroyLeadFile(Request $request)
    {
        $file = File::find($request->id);
        if (!$file) {

            return response()->json([
                'status' => true,
                'message' => 'File destroyed Sucessfully'
            ], 200);
        }
        $lead= $file->uploaded_files;
      
        create_log($lead, 'Delete File : '. $file->name,'');

        $file->delete();
        return response()->json([
            'status' => true,
            'message' => 'File destroyed Sucessfully'
        ], 200);
    }

    public function fetchRequiredContactInfo()
    {
        $states = Lead::Lead_States();
        $counties = Lead::Lead_Counties();
        $statusOptions = parent::getContactStatusOptions();
        $leadSource = LeadSource::select('id as key', 'name as value')->where('status', 1)->get();

        $typesWithCarriersAndRatings = [
            'Property' => ['carrierVar' => 'carriersWithProperty', 'ratingVar' => 'ratingsWithProperty'],
            'General Liability' => ['carrierVar' => 'carriersWithGeneralLiability', 'ratingVar' => 'ratingsWithGeneralLiability'],
            'Crime Insurance' => ['carrierVar' => 'carriersWithCrimeInsurance', 'ratingVar' => 'ratingsWithCrimeInsurance'],
            'Directors & Officers' => ['carrierVar' => 'carriersWithDirectorOfficor', 'ratingVar' => 'ratingsWithDirectorOfficor'],
            'Umbrella' => ['carrierVar' => 'carriersWithUnbrella', 'ratingVar' => 'ratingsWithUnbrella'],
            'Workers Compensation' => ['carrierVar' => 'carriersWithWorkCompensation', 'ratingVar' => 'ratingsWithWorkCompensation'],
            'Flood' => ['carrierVar' => 'carriersWithFlood', 'ratingVar' => 'ratingsWithFlood'],
        ];

        foreach ($typesWithCarriersAndRatings as $name => $vars) {
            $type = InsuranceType::where('name', $name)->first();
            if ($type) {
                ${$vars['carrierVar']} = $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray();
                ${$vars['ratingVar']} = $type->ratings()->where('status', 1)->pluck('ratings.name', 'ratings.id')->toArray();
            } else {
                ${$vars['carrierVar']} = collect();
                ${$vars['ratingVar']} = collect();
            }
        }

        $typesWithOnlyCarriers = [
            'Difference In Conditions' => 'carriersWithDifference',
            'X-Wind' => 'carriersWithXwind',
            'Equipment Breakdown' => 'carriersWithEquipment',
            'Commercial AutoMobile' => 'carriersWithCommercial',
            'Marina' => 'carriersWithMarina',
        ];

        foreach ($typesWithOnlyCarriers as $name => $varName) {
            $type = InsuranceType::where('name', $name)->first();
            ${$varName} = $type ? $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray() : collect();
        }

        $additionalCarrier = [];
        foreach ($this->additionalPoliciesCarrier as $key => $policy) {
            $type = InsuranceType::where('name', $key)->first();
            $additionalCarrier[$key] = $type ? $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray() : collect();
        }

        return response()->json([
            'status' => true,
            'message' => 'Sucessfully',
            'states' => $states,
            'counties' => $counties,
            'statusOptions' => $statusOptions,
            'leadSource' => $leadSource,

            // Carriers and Ratings
            'carriersWithProperty' => $carriersWithProperty,
            'ratingsWithProperty' => $ratingsWithProperty,
            'carriersWithGeneralLiability' => $carriersWithGeneralLiability,
            'ratingsWithGeneralLiability' => $ratingsWithGeneralLiability,
            'carriersWithCrimeInsurance' => $carriersWithCrimeInsurance,
            'ratingsWithCrimeInsurance' => $ratingsWithCrimeInsurance,
            'carriersWithDirectorOfficor' => $carriersWithDirectorOfficor,
            'ratingsWithDirectorOfficor' => $ratingsWithDirectorOfficor,
            'carriersWithUnbrella' => $carriersWithUnbrella,
            'ratingsWithUnbrella' => $ratingsWithUnbrella,
            'carriersWithWorkCompensation' => $carriersWithWorkCompensation,
            'ratingsWithWorkCompensation' => $ratingsWithWorkCompensation,
            'carriersWithFlood' => $carriersWithFlood,
            'ratingsWithFlood' => $ratingsWithFlood,

            // Only Carriers
            'carriersWithDifference' => $carriersWithDifference,
            'carriersWithXwind' => $carriersWithXwind,
            'carriersWithEquipment' => $carriersWithEquipment,
            'carriersWithCommercial' => $carriersWithCommercial,
            'carriersWithMarina' => $carriersWithMarina,

            'additionalPolicies' => $this->additionalPoliciesCarrier,
            'additionalCarrier' => $additionalCarrier,
        ], 200);
    }

    public function updateContact(Request $request)
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
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $c_agent_id = $request->c_agent_id;
        // $all_account_list_permission = auth()->user()->can('all-accounts-list-pipedrive');
        // if(!$all_account_list_permission){
        //     $c_agent_id = auth()->user()->id;
        // }
        // else{
        //     if(!empty($request->c_agent_id)){
        //         $c_agent_id = $request->c_agent_id;
        //     }
        // }
        if(!empty($request->c_status)){
            $agent_type_status = ContactStatus::where('id',$request->c_status)->first();
            if($agent_type_status && $agent_type_status->status_type == 2){
                if(empty($c_agent_id)){
                    return response()->json([
                        'status' => false,
                        'message' => "Selecting an agent is mandatory with ".$agent_type_status->name." status",
                    ], 200);
                }
                
            }
            // unset($agent_type_status);
        }

        $contact = Contact::find($request->contact_id);
        if(!$contact){
            return response()->json([
                'status' => false,
                'message' => "Failed to update",
            ], 200);
        }
        // $old_status_id = $contact->c_status;

        $contact->c_first_name = $request->c_first_name;
        $contact->c_last_name = $request->c_last_name;
        $contact->c_full_name = $request->c_first_name . ' ' . $request->c_last_name;
        $contact->c_title = $request->c_title;
        $contact->c_address1 = $request->c_address1;
        $contact->c_address2 = $request->c_address2;
        $contact->c_city = $request->c_city;
        $contact->c_state = $request->c_state;
        $contact->c_county = $request->c_county;
        $contact->c_zip = $request->c_zip;
        $contact->c_phone = $request->c_phone;
        $contact->c_email = $request->c_email;
        $contact->c_status = $request->c_status;
        $contact->c_agent_id = $c_agent_id;
        $contact->save();

        // $new_status_id = $contact->c_status;
        // if($old_status_id != $new_status_id){
        //     $this->statusChangeMsgShoot($lead, $c_agent_id, $old_status_id, $new_status_id);
        // }       

        $this->contactbasedleadstatusupdate($request->lead_id,$contact->c_agent_id,$request->c_status);

        $agent_type_status = ContactStatus::where('id',$request->c_status)->first();

        $lead = Lead::find($request->lead_id);

        if (!empty($request->c_status) && $agent_type_status) {

            // $contact->update(['status' => $request->c_status]);
            // $workableStatus = [
            //  'Bad Number', 'Do Not Call', 'Not Interested', 'Call Back',
            //  'No Answer (Left Message)', 'Policies Received', 'AOR Received', 'Select Status'
            // ];
            // if (in_array($request->c_status, $workableStatus)) :
            if(empty($agent_type_status->false_status)){
                $own_status = $agent_type_status->display_in_pipedrive;
                $this->updateDialingLists($request->c_status, $contact->id, $request->lead_id,$contact->c_agent_id,$own_status);
                $this->setContactToQueue($lead);

                $message = auth()->user()->name . ' has updated status of contact : ' . $contact->id . ' to ' . $request->c_status . ' present in lead: ' . $request->lead_id;
                Agentlog::updateOrCreate(
                    ['user_id' => auth()->user()->id, 'contact_id' => $contact->id],
                    ['message' => $message, 'user_id' => auth()->user()->id, 'lead_id' => $request->lead_id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
                );
            }
            // endif;
        }

        // $this->updateContactShoot($lead,$c_agent_id,$contact->c_first_name,$contact->c_last_name,$request->c_status);

        $res = $this->leadiDBasedData($request->lead_id);
        create_log($lead, 'Edit Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');

        return response()->json([
            'status' => true,
            'message' => "Updated",
            'lead' =>  $res['lead'],
            'status_list' =>  $res['status_list'],
            'additonalPolicy' =>  $res['additonalPolicy'],
        ], 200);

    }

    public function addContact(Request $request)
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
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $c_agent_id = $request->c_agent_id;
        // $all_account_list_permission = auth()->user()->can('all-accounts-list-pipedrive');
        // if(!$all_account_list_permission){
        //     $c_agent_id = auth()->user()->id;
        // }
        // else{
        //     if(!empty($request->c_agent_id)){
        //         $c_agent_id = $request->c_agent_id;
        //     }
        // }
        if(!empty($request->c_status)){
            $agent_type_status = ContactStatus::where('id',$request->c_status)->first();
            if($agent_type_status && $agent_type_status->status_type == 2){
                if(empty($c_agent_id)){
                    return response()->json([
                        'status' => false,
                        'message' => "Selecting an agent is mandatory with ".$agent_type_status->name." status",
                    ], 200);
                }
                
            }
            // unset($agent_type_status);
        }

        $lead = Lead::find($request->lead_id);


        $contact = new Contact();
        $contact->c_first_name = $request->c_first_name;
        $contact->c_last_name = $request->c_last_name;
        $contact->c_full_name = $request->c_first_name . ' ' . $request->c_last_name;
        $contact->c_title = $request->c_title;
        $contact->c_address1 = $request->c_address1;
        $contact->c_address2 = $request->c_address2;
        $contact->c_city = $request->c_city;
        $contact->c_state = $request->c_state;
        $contact->c_county = $request->c_county;
        $contact->c_zip = $request->c_zip;
        $contact->c_phone = $request->c_phone;
        $contact->c_email = $request->c_email;
        $contact->lead_id = $request->lead_id;
        $contact->c_status = $request->c_status;
        $contact->c_agent_id = $c_agent_id;
        $contact->save();

        $contact->leads()->associate($lead);

        $this->contactbasedleadstatusupdate($request->lead_id,$contact->c_agent_id,$request->c_status);

        $agent_type_status = ContactStatus::where('id',$request->c_status)->first();

        if (!empty($request->c_status) && $agent_type_status) {
            $own_status = $agent_type_status->display_in_pipedrive;
            $this->updateDialingLists($request->c_status, $contact->id, $request->lead_id,$contact->c_agent_id,$own_status);
            $this->setContactToQueue($lead);

            $message = User::where("id",$contact->c_agent_id)->value("name") . ' has updated status of contact : ' . $contact->id . ' to ' . $request->c_status . ' present in lead: ' . $request->lead_id;
            Agentlog::updateOrCreate(
                ['user_id' => $contact->c_agent_id, 'contact_id' => $contact->id],
                ['message' => $message, 'user_id' => $contact->c_agent_id, 'lead_id' => $request->lead_id, 'contact_id' => $contact->id, 'status' => 'call_status_updated']
            );
        }

        create_log($lead, 'Create Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');

        $res = $this->leadiDBasedData($request->lead_id);

        // $this->newContactAdditionWithLeadShoot($lead,$c_agent_id,$contact->c_first_name,$contact->c_last_name,$request->c_status);

        return response()->json([
            'status' => true,
            'message' => "Updated",
            'lead' =>  $res['lead'],
            'status_list' =>  $res['status_list'],
            'additonalPolicy' =>  $res['additonalPolicy'],
        ], 200);
    }

    public function removeContact(Request $request)
    {
        $contact = Contact::find($request->id);
        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => "Failed to update",
            ], 200);
        }

        $lead_id = $contact->leads->id;

        $contact->delete();

        $this->contactbasedleadstatusupdate($lead_id,0,0);

        $lead = Lead::find($lead_id);
        create_log($lead, 'Delete Contact : ' . $contact->c_first_name . ' ' . $contact->c_last_name, '');

        $pipeline_status_id = $lead->pipeline_status_id;
        $pipeline_agent_id = $lead->pipeline_agent_id;

        $this->updateDialingLists($pipeline_status_id, 0, $lead_id,$pipeline_agent_id);

        $res = $this->leadiDBasedData($lead_id);

        return response()->json([
            'status' => true,
            'message' => "Updated",
            'lead' =>  $res['lead'],
            'status_list' =>  $res['status_list'],
            'additonalPolicy' =>  $res['additonalPolicy'],
        ], 200);

    }

    public function updateIndiLeadData(Request $request)
    {
        $request->validate([
            'leadId' => 'required|integer|exists:leads,id',
            'dbFieldName' => 'required|string',
            // 'value' => 'nullable|string',
        ]);

        $lead = Lead::find($request->leadId);

        if ($lead) {
            $fieldvalue = $request->value;
            if ($request->otherPermission && $fieldvalue == "other") {
                $fieldvalue = $request->otherValueField;

                if (!empty($request->catName)) {
                    if (!empty($request->requestType) && $request->requestType == 1) {
                        $fieldvalue = $this->makelogInCarrierTable($request->otherValueField, $request->catName);
                        $res_array[$request->catDbField] = $request->otherValueField;
                    }
                    else if (!empty($request->requestType) && $request->requestType == 2) {
                        $fieldvalue = $this->makelogInRatingTable($request->otherValueField, $request->catName);
                        $res_array[$request->catDbField] = $request->otherValueField;
                    }
                }
            }
            else{
                if (!empty($request->catName)) {
                    if (!empty($request->requestType) && $request->requestType == 1) {
                        $res_array[$request->catDbField] = Carrier::where('id', $fieldvalue)->pluck('name')->first() ?? "";
                    }
                    else if (!empty($request->requestType) && $request->requestType == 2) {
                        $res_array[$request->catDbField] = Rating::where('id', $fieldvalue)->pluck('name')->first() ?? "";
                    }
                    else if (!empty($request->requestType) && $request->requestType == 3) {
                        $res_array[$request->catDbField] = LeadSource::where('id', $fieldvalue)->pluck('name')->first() ?? "";
                    }
                }
            }

            $lead->{$request->dbFieldName} = $fieldvalue;
            $res_array[$request->dbFieldName] = $fieldvalue;

            $lead->save();

            create_log($lead, 'updated  lead : column - ' . $request->dbFieldName . '  value - ' . $request->value, '');

            return response()->json([
                'status' => true,
                'message' => 'Lead updated successfully',
                'res_array' => $res_array
            ]);
        } else {
            // Return an error response if the lead is not found
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }
    }

    public function updateIndiLeadDataGroup(Request $request)
    {
        $request->validate([
            'leadId' => 'required|integer|exists:leads,id',
        ]);

        $lead = Lead::find($request->leadId);

        if ($lead) {
            $res_array = [];

            foreach ($request->input('inputdata', []) as $value) {  
                $fieldvalue = $value['value'];
                $dbFieldName = $value['dbFieldName'];

                if ($value['otherPermission'] && $fieldvalue == "other") {
                    $fieldvalue = $value['otherValueField'];

                    if (!empty($value['catName'])) {
                        if (!empty($value['requestType']) && $value['requestType'] == 1) {
                            $fieldvalue = $this->makelogInCarrierTable($value['otherValueField'], $value['catName']);
                            $res_array[$value['catDbField']] = $value['otherValueField'];
                        }
                    }
                }
                else{
                    if (!empty($value['catName'])) {
                        if (!empty($value['requestType']) && $value['requestType'] == 1) {
                            $res_array[$value['catDbField']] = Carrier::where('id', $fieldvalue)->pluck('name')->first() ?? "";
                        }
                    }
                }

                $lead->{$dbFieldName} = $fieldvalue;
                $res_array[$dbFieldName] = $fieldvalue;

                create_log($lead, 'Updated lead: column - ' . $dbFieldName . ' value - ' . $fieldvalue, '');
            }

            $lead->save();

            $this->leadTotalPremiumUpdate($lead->id);

            return response()->json([
                'status' => true,
                'message' => 'Lead updated successfully',
                'res_array' => $res_array
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }
    }

    public function updateAdditionalPolicyLead(Request $request)
    {
        $request->validate([
            'leadId' => 'required',
            'policy_type' => 'required',
            'carrier' => 'required',
        ]);

        if(!empty($request->item_id)){
            $additional = LeadAdditionalPolicy::find($request->item_id);
            if(!$additional){
                return response()->json([
                    'status' => false,
                    'message' => 'Additional',
                ],200);
            }
        }
        else{
            $additional = new LeadAdditionalPolicy();
            $additional->lead_id = $request->leadId;
        }
        if($request->carrier === "other"){
            $carrier_id = $this->makelogInCarrierTable($request->carrierOther,$request->policy_type);
        }
        else{
            $carrier_id = $request->carrier;
        }
        $additional->carrier = $carrier_id;
        $additional->policy_type = $request->policy_type;
        $additional->expiry_premium = $request->expiry_premium;
        $additional->policy_renewal_date = $request->policy_renewal_date;
        $additional->hurricane_deductible = $request->hurricane_deductible;
        $additional->all_other_perils = $request->all_other_perils_deductible;
        $additional->insurance_coverage = $request->notes;

        $additional->save();

        $res = $this->leadiDBasedData($request->leadId);

        $this->leadTotalPremiumUpdate($request->leadId);

        return response()->json([
            'status' => true,
            'message' => 'Additional',
            'additonalPolicy' =>  $res['additonalPolicy'],
        ],200);
    }

    public function deleteAdditionalPolicyLead(Request $request)
    {
        $request->validate([
            'leadId' => 'required',
        ]);

        if(!empty($request->item_id)){
            $additional = LeadAdditionalPolicy::find($request->item_id);
            if($additional){
                $additional->delete();
            }
        }

        $res = $this->leadiDBasedData($request->leadId);

        return response()->json([
            'status' => true,
            'message' => 'Additional',
            'additonalPolicy' =>  $res['additonalPolicy'],
        ],200);
    }


    public function asanaStatusList()
    {
        $question = AsanaQuestion::select('id','name')->where("status",1)->orderBy("priority","ASC")->get();

        return response()->json([
            'status' => true,
            'message' => 'question',
            'question' =>  $question,
        ],200);

    }

    public function makelogLeadAsanaWise($LeadAsanaDetail)
    {
        $leadInfoLog = new LeadInfoLog();
        $leadInfoLog->lead_id = $LeadAsanaDetail['lead_id'];
        $leadInfoLog->table_name = 'LeadAsanaDetail';
        $leadInfoLog->renewal_date = $LeadAsanaDetail['renewal_date'];
        $leadInfoLog->data = json_encode($LeadAsanaDetail);
        $leadInfoLog->save();
        unset($leadInfoLog);

        $lead = Lead::where('id',$LeadAsanaDetail['lead_id'])->first();
        if($lead){
            $additonalPolicy = $lead->leadAdditionalpolicy()->get()->toArray();
            $lead = $lead->toArray();
            $leadLog = new LeadInfoLog();
            $leadLog->lead_id = $LeadAsanaDetail['lead_id'];
            $leadLog->table_name = 'Lead';
            $leadLog->renewal_date = $LeadAsanaDetail['renewal_date'];
            $leadLog->data = json_encode($lead);
            $leadLog->save();

            unset($leadLog);

            $leadLog = new LeadInfoLog();
            $leadLog->lead_id = $LeadAsanaDetail['lead_id'];
            $leadLog->table_name = 'LeadAdditionalPolicy';
            $leadLog->renewal_date = $LeadAsanaDetail['renewal_date'];
            $leadLog->data = json_encode($additonalPolicy);
            $leadLog->save();

            unset($leadLog);
        }
    }

    public function checkLeadNeedToRenewal()
    {
        $setting_time_data = Setting::select('renewal_days_in_pipeline')->first();
        $subDays = !empty($setting_time_data->renewal_days_in_pipeline)?$setting_time_data->renewal_days_in_pipeline:0;
        $leadList = LeadAsanaDetail::where('lead_asana_details.asana_stage',11)
            ->where('lead_asana_details.stage_completed',1)
            ->where('lead_asana_details.renewal_date','<=',Carbon::today()->addDays($subDays))
            // ->select('lead_asana_details.lead_id')
            ->limit(10)
            ->get()->toArray();

        foreach ($leadList as $keylead) {
            $this->makelogLeadAsanaWise($keylead);
            $this->clearLeadAsanaDetailTable($keylead['id']);


            $this->clearLeadDetailTable($keylead['lead_id']);
        }



        // echo "<pre>";
        // print_r($leadList);
        // exit;
    }

    public function differentQuestionWiseLead(Request $request)
    {
        $asana_stage = $request->input('asana_stage');
        $agentId = $request->input('agentId');
        $filterData = !empty($request->input('filterData')) ? json_decode(json_encode($request->input('filterData'))) : (object)[];
        $pageNumber = $request->input('page_number', 1);
        $pageSize = $request->input('page_size', 10);
        $sortBy = $request->input('sortBy');
        $orderBy = $request->input('orderBy', 'asc');

        $statusId = $this->getSignedAorStatusId();

        if ($asana_stage == 1) {
            $this->checkLeadNeedToRenewal();

            $leadList = Lead::join('users', 'leads.pipeline_agent_id', '=', 'users.id')
                ->leftJoin('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                ->where(function ($query) use ($statusId) {
                    $query->where('lead_asana_details.asana_stage', 1)
                          ->orWhereNull('lead_asana_details.lead_id');
                })
                ->where('leads.pipeline_status_id', $statusId);
        } else {
            $leadList = Lead::join('lead_asana_details', 'leads.id', '=', 'lead_asana_details.lead_id')
                ->join('users', 'leads.pipeline_agent_id', '=', 'users.id')
                ->where('lead_asana_details.asana_stage', $asana_stage)
                ->where('lead_asana_details.stage_completed', 0)
                ->where('leads.pipeline_status_id', $statusId);;
        }

        if (!empty($sortBy)) {
            if ($sortBy === 'name') {
                $leadList->orderBy('leads.name', $orderBy);
            } elseif ($sortBy === 'date') {
                $leadList->orderBy('status14_start_timestamp', $orderBy);
            } elseif ($request->input('sortBy') == 'updated') {
                $leadList->orderBy('leads.updated_at', $request->input('orderBy', 'asc'));
            } elseif ($sortBy === 'premium') {
                $leadList->orderBy('leads.total_premium', $orderBy);
            }
        }

        $leadList->leftJoin('lead_status_wise_log as lswl', function ($join) {
            $join->on('leads.id', '=', 'lswl.lead_id')
                ->where('lswl.status_id', '=', 14);
        });

        if (!empty($agentId)) {
            $leadList->where(function($query) use ($agentId) {
                $query->where('leads.pipeline_agent_id', $agentId)
                ->orWhere('leads.assigned_user_id', $agentId);
            });
        }

        if (!empty($filterData)) {
            if (!empty($filterData->name)) {
                $leadList->where('leads.name', 'like', '%' . $filterData->name . '%');
            }
            // if (!empty($filterData->agent_id)) {
            //     $leadList->where('leads.pipeline_agent_id', $filterData->agent_id);
            // }
        }

        $totalInsuredAmount = $leadList->sum('total_premium');

        $selectFields = [
            'leads.id',
            'leads.name',
            'leads.address1',
            'leads.updated_at',
            'lead_asana_details.stage_completed',
            'leads.total_premium as insured_amount',
            'lead_asana_details.renewed_lead',
            'lead_asana_details.renewal_date',
            'leads.assigned_user_id',
            'leads.pipeline_agent_id',
            'leads.total_premium',
            'users.name as pipeline_agent_name',
            'users.email as pipeline_agent_email',
            'lswl.start_timestamp as status14_start_timestamp',
        ];

        // Conditionally add agent info
        // if ($agentId == 0) {
            // $selectFields[] = 'users.name as pipeline_agent_name';
            // $selectFields[] = 'users.email as pipeline_agent_email';
        // }
        $leadList = $leadList
            ->select($selectFields)
            ->paginate($pageSize, ['*'], 'page', $pageNumber);

        $leadList->getCollection()->load([
            'collaborators:id,name,email',
            'assignedUser:id,name,email'
        ]);

        foreach ($leadList->items() as $lead) {
            $lead->assigned_user_custom = $lead->customUserGetting();
        }

        return response()->json([
            'status' => true,
            'message' => 'Leads retrieved successfully',
            'lead_list' => $leadList->getCollection(),
            'pagination' => [
                'total' => $leadList->total(),
                'current_page' => $leadList->currentPage(),
                'last_page' => $leadList->lastPage(),
                'per_page' => $leadList->perPage(),
                'totalInsuredAmount' => $totalInsuredAmount
            ],
        ], 200);

    }

    public function getquestionAsaan($leadId)
    {
        $lead_asana = LeadAsanaDetail::where('lead_id',$leadId)->first();

        $asana_stage = !empty($lead_asana->asana_stage)?$lead_asana->asana_stage:1;
        $stage_completed = !empty($lead_asana->stage_completed)?$lead_asana->stage_completed:0;
        $asana_priority = 0;

        $questions = AsanaQuestion::select('id','name','priority')->where("status",1)->orderBy("priority","ASC")->get();
        foreach ($questions as $keyquestion) {
            if($keyquestion->id == $asana_stage){
                $asana_priority = $keyquestion->priority;
            }
            $keyquestion->leadId = $leadId;
            $stagewise_question  = AsanaQuestionDetail::where('asana_question_id',$keyquestion->id)->select('question','id','short_hand','show_selection','selection_category','category_type','selection_short_hand')->get();
            foreach ($stagewise_question as $keystage) {
                $keystage->answer = !empty($lead_asana->{$keystage->short_hand})?$lead_asana->{$keystage->short_hand}:'';
                $keystage->selection_list = !empty($lead_asana->{$keystage->selection_short_hand})?$lead_asana->{$keystage->selection_short_hand}:'';
            }
            $keyquestion->stagewise_question = $stagewise_question;
        }

        return [
            'questions' => $questions,
            'asana_stage' => $asana_stage,
            'stage_completed' => $stage_completed,
            'asana_priority' => $asana_priority,
        ];
    }

    public function carrierListDisplay()
    {
        $carrier_list = ["Property","General Liability","Directors & Officers","Legal Defense","Umbrella","Crime Insurance","Workers Compensation","Flood"];

        $list = [];

        foreach ($carrier_list as $key => $carrier) {
            $ins["name"] = $carrier;
            $type = InsuranceType::where('name', $carrier)->first();
            if ($type) {
                $ins['carrierlist'] = $type->carriers()->where('status', 1)->pluck('carriers.name', 'carriers.id')->toArray();
            } else {
                $ins['carrierlist'] = collect();
            }

            $list[] = $ins;
        }

        return $list;
    }

    public function leadAsanaDetails(Request $request)
    {
        $leadId = $request->input('lead_id');

        $details = $this->getquestionAsaan($leadId);
        $carrier = $this->carrierListDisplay();

        return response()->json([
            'status' => true,
            'message' => 'Lead Asana Details',
            'carrier' => $carrier,
            'details' => $details['questions'],
            'asana_stage' => $details['asana_stage'],
            'stage_completed' => $details['stage_completed'],
            'asana_priority' => $details['asana_priority'],
        ], 200);
    }

    public function getAsanaStagePriority($asana_stage_id)
    {
        $asanaStage = AsanaQuestion::select('priority')->find($asana_stage_id);

        if (!$asanaStage) {
            return null;
        }

        $new_priority = $asanaStage->priority + 1;

        return AsanaQuestion::where('priority', $new_priority)->value('id');
    }

    public function updateleadAsanaDetails(Request $request)
    {
        $leadId = $request->input('leadId');
        $stagewise_question = $request->input('stagewise_question');
        // echo $request->input('renewalDate');exit;

        $close_model = false;

        $lead_asana = LeadAsanaDetail::where('lead_id',$leadId)->first();
        if(!$lead_asana){
            $lead_asana = new LeadAsanaDetail();
            $lead_asana->lead_id = $leadId;
            $lead_asana->asana_stage = 1;
            $lead_asana->last_updated_date = Carbon::now()->toDateString();
        }
        foreach ($stagewise_question as $keyquestion) {
            // echo "<pre>";print_r($stagewise_question);exit;
            $lead_asana->{$keyquestion['short_hand']} = $keyquestion['answer'];
            if($keyquestion['show_selection']){
                if($keyquestion['answer'] == "yes"){
                    $lead_asana->{$keyquestion['selection_short_hand']} = $keyquestion['selection_list'];
                }
            }
        }
        if($lead_asana->asana_stage == $request->input('id')){
            $lead_asana->last_updated_date = Carbon::now()->toDateString();
            if($lead_asana->asana_stage < 11){
                $lead_asana->asana_stage = $this->getAsanaStagePriority($lead_asana->asana_stage);
            }
            else{
                if(!empty($request->input('renewalDate'))){
                    $lead_asana->stage_completed = 1;
                }
                $lead_asana->renewal_date = $request->input('renewalDate');
                $close_model = true;
            }
            $this->asanaStageChangeMsgShoot($leadId, $lead_asana->asana_stage);
        }
        $lead_asana->save();

        $details = $this->getquestionAsaan($leadId);

        return response()->json([
            'status' => true,
            'message' => 'Lead Asana Updated Sucessfully',
            'leadId' =>  $leadId,
            'stagewise_question' =>  $stagewise_question,
            'close_model' =>  $close_model,
            'asana_stage' =>  $lead_asana->asana_stage,
            'details' => $details['questions'],
            'asana_stage' => $details['asana_stage'],
            'asana_priority' => $details['asana_priority'],
            'stage_completed' => $details['stage_completed'],
        ], 200);
    }

    public function updateTestingDialingContact($dialing_id)
    {
        $leads = DB::table('dialings_leads')->select('lead_id')->where('dialing_id',$dialing_id)->get();

        foreach ($leads as $keylead) {
            $additional_info = DB::table('additionleadid3')->where('lead_id',$keylead->lead_id)->where('phone','!=','')->first();

            $leads_table = Lead::select('address1','id')->where('id',$keylead->lead_id)->first();
            if($additional_info && $leads_table){
                $contactchecking = DB::table('contacts')->where('lead_id',$keylead->lead_id)
                ->where('c_first_name',$additional_info->firstname)->where('c_last_name',$additional_info->lastname)->first();
                if($contactchecking){
                    $this->updateContactleadsData($contactchecking,$additional_info,$leads_table,1);
                }
                else{
                    $contactchecking = DB::table('contacts')->where('lead_id',$keylead->lead_id)
                    ->where('c_first_name',$additional_info->lastname)->where('c_last_name',$additional_info->firstname)->first();
                    if($contactchecking){
                        $this->updateContactleadsData($contactchecking,$additional_info,$leads_table,1);
                    }
                    else{
                        $this->updateContactleadsData($contactchecking,$additional_info,$leads_table,2);
                    }
                }
            }
        }

        echo "Done";
        
    }

    public function updateContactleadsData($contactchecking,$additional_info,$leads_table,$type)
    {
        if($type == 1){
            $contact = Contact::withTrashed()->where('id', $contactchecking->id)->first();

            if ($contact) {
                if (!is_null($contact->deleted_at)) {
                    $contact->restore(); // Restore the soft-deleted record
                }

                $contact->c_phone = $additional_info->phone;
                if($additional_info->email){
                    $contact->c_email = $additional_info->email;
                }
                if(empty($contact->fake_address)){
                    $contact->fake_address = 2;
                }
                $contact->save();
            }
        }
        else{
            $contact = new Contact();
            $contact->lead_id = $leads_table->id;
            $contact->c_first_name = $additional_info->firstname;
            $contact->c_last_name = $additional_info->lastname;
            $contact->c_full_name = $contact->c_first_name." ".$contact->c_last_name;
            $contact->c_address1 = $leads_table->address1;
            $contact->fake_address = 1;
            $contact->c_phone = $additional_info->phone;

            $addressWithNumber = $leads_table->address1;
            if (preg_match('/\d+/', $leads_table->address1, $matches)) {
                $addressWithNumber = $matches[0];
            }
            $contactSlug = $this->generateSlug([$additional_info->firstname, $additional_info->lastname, $addressWithNumber]);
            $contact->contact_slug = $contactSlug;
            if($additional_info->email){
                $contact->c_email = $additional_info->email;
            }
            $contact->save();
        }
    }

    public function updated_dialing($dialing_id)
    {
        $leads = DB::table('dialings_leads')->select('lead_id')->where('dialing_id',$dialing_id)->get();
        $agents = [38,32,39,26,33,31];
        foreach ($leads as $key =>  $lead) {
            $index = $key % 5;

            DB::table('dialings_leads')->where('dialing_id',$dialing_id)->where('lead_id',$lead->lead_id)->update([
                'assigned_to_agent_id' => $agents[$index]
            ]);
        }

        echo "DOne";
    }

    public function updateLeadStatusPipeline($status,$agent_id)
    {
        $leadInfo = DB::table('additionleadid9')->where('lead_id','!=','')->where('status_id','!=',0)->get();

        foreach($leadInfo as $keylead){
            // echo "<pre>";print_r($keylead);
            $contactchecking = Contact::where('lead_id',$keylead->lead_id)
            ->where('c_first_name',$keylead->firstname)->where('c_last_name',$keylead->lastname)
            ->first();

            // echo "<pre>";print_r($contactchecking);exit;
            if($contactchecking){
                // if(empty($status)){
                    $status = $keylead->status_id;
                // }
                $contactchecking->c_status = $status;
                $contactchecking->c_agent_id = $agent_id;
                $contactchecking->save();

                Lead::where('id',$keylead->lead_id)->update([
                    "pipeline_status_id" => $status,
                    "pipeline_agent_id" => $agent_id,
                ]);

                DB::table('additionleadid9')->where('id',$keylead->id)->update([
                    "status" => 1,
                ]);
            }
            else{
                DB::table('additionleadid9')->where('id',$keylead->id)->update([
                    "status" => 2,
                ]);
            }
        }

        echo "DOne";
    }

    public function getCollaboratorDetails(Request $request)
    {
        $leadId = $request->input('leadId');

        $lead = Lead::find($leadId);

        if (!$lead) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 400);
        }

        $userList = User::role(['Agent','Service & Agent', 'Admin', 'Super Admin','Service Team','Manager'])->select('id', 'name', 'email')->get();

        $collabList = $lead->collaborators()
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        $collabArr = $collabList->pluck('id')->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Collaborator Details',
            'collabList' => $collabList,
            'collabArr' => $collabArr,
            'userList' => $userList,
        ]);
    }

    public function updateCollaboratorDetails(Request $request)
    {
        $leadId = $request->input('leadId');
        $collabUserIds = $request->input('collabUserIds', []);

        $lead = Lead::find($leadId);
        if (!$lead) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 400);
        }

        $lead->collaborators()->sync($collabUserIds);

        $lead = $lead->fresh('collaborators:id,name,email');

        return response()->json([
            'status' => true,
            'message' => 'Collaborators updated successfully',
            "collaborators" => $lead->collaborators
        ]);
    }

    public function updateAssignee(Request $request)
    {
        $leadId = $request->input('leadId');
        $assignee = $request->input('assignee');
        $screenType = $request->input('screenType');

        $lead = Lead::with([
            'assignedUser:id,name,email',
            'leadAsanaDetail:id,lead_id,asana_stage',
            'collaborators:id,name,email'
        ])->find($leadId);

        if (!$lead) {
            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $lead->assigned_user_id = $assignee;
        $lead->save();

        $lead = $lead->fresh('assignedUser:id,name,email');

        $assignedUser = $lead->assignedUser;

        $assigned_user_name = "";
        $assigned_user_id = 0;

        if(!$assignedUser){
            $assignedUser = $lead->customUserGetting();
        }

        if(!empty($assignedUser->name)){
            $assigned_user_name = $assignedUser->name;
            $assigned_user_id = $assignedUser->id;

            $this->assigneeAddedMessageShoot($assigned_user_name,$assigned_user_id,$lead,$screenType);
        }

        $this->makeLogForAssigneeAgent($assigned_user_id,$lead);

        return response()->json([
            'status' => true,
            'message' => "{$lead->name} assignee has been updated",
            'assignee_user' => $assignedUser
        ]);
    }

    public function contactChat($contactId)
    {
        $user = auth()->user();
        // echo "<pre>";print_r($user);exit;
        $agentWisePermission = $this->getAgentWisePermission($user);

        $isAdminUser = $agentWisePermission["isAdminUser"];
        $agentId = $isAdminUser ? 0 : $user->id;

        $messages = Message::select('users.name','messages.chat_type','messages.chat_sms_sent_status','messages.content','messages.created_at');

        if($agentId){
            $messages->where(function($query) use($agentId) {
                $query->where('messages.user_id', $agentId)
                      ->orWhere('messages.user_id', 0)
                      ->orWhereNull('messages.user_id');
            });
        }

        if (!empty($newsletter_type) && strtolower($newsletter_type) == 'yes') {
            $messages->where('messages.newsletter_id', $contactId);
        } else {
            $messages->where('messages.contact_id', $contactId);
        }
        $messages->leftjoin('users','messages.user_id','=','users.id');

        // Execute the query and get the collection
        $messages = $messages->orderBy('messages.created_at')->get();

        // Return JSON response with the collection (no conversion to array)
        return response()->json([
            'status' => '200',
            'response' => $messages, // Return the collection directly
            'contact_id' => $contactId,
            'is_admin' => $isAdminUser,
            'unread_count' => 0
        ],200);
    }

    public function sendChat(Request $request)
    {
        $chatContactId = $request->input('chatContactId');
        $chatAgentId   = $request->input('chatAgentId');
        $chatContent   = $request->input('content');

        $user = User::findOrFail($chatAgentId);
        $agentWisePermission = $this->getAgentWisePermission($user);
        $isAdminUser = $agentWisePermission["isAdminUser"];

        // Check if chat is stopped
        $contact = Contact::find($chatContactId);
        if ($contact && $contact->has_initiated_stop_chat) {
            return response()->json([
                'success' => false,
                'response' => "Cannot send message, chat has ended.",
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
            ], 403);
        }

        // Check max execution time
        $checkMaxTimeResponse = $this->check_max_execution_time($chatContactId);
        $responseData = json_decode($checkMaxTimeResponse->getContent(), true);

        if (!empty($responseData['response']) && $responseData['response'] > 0) {
            return response()->json([
                'success' => false,
                'left_minute' => $responseData['response'],
                'response' => "You can't send the message before {$responseData['response']} mins.",
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
            ], 429);
        }

        // Append STOP message if first outbound message
        $firstOutbound = Message::where('chat_type', 'outbound')
            ->where('contact_id', $chatContactId)
            ->first();

        if (is_null($firstOutbound)) {
            $chatContent .= '</br> Please text "STOP" to stop the conversation.';
        }

        // Save the message
        try {
            $settings = Setting::find(1);
            $proceedTime = $settings->proceed_time_in_minute ?? 0;

            $message = new Message();
            $message->user_id = auth()->id();
            $message->contact_id = $chatContactId;
            $message->through_sms_provider_flag = 1;
            $message->max_time_to_send = Carbon::now()->addMinutes($proceedTime);
            $message->content = $chatContent;
            $message->chat_type = 'outbound';
            $message->save();

            $lastEntry = [
                "name" => $user->name,
                "chat_type" => $message->chat_type,
                "chat_sms_sent_status" => $message->chat_sms_sent_status ?? null,
                "content" => $message->content,
                "created_at" => $message->created_at,
            ];

            return response()->json([
                'success' => true,
                'response' => $message,
                'last_insert_id' => $message->id,
                'contact_id' => $chatContactId,
                'unread_count' => 0,
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
                'last_entry' => $lastEntry,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'response' => "Failed to send the message. Please contact the administrator.",
                'is_admin' => $isAdminUser,
                'logged_in_user_name' => $user->name,
                'error' => $e->getMessage(), // Optional for debugging
            ], 500);
        }
    }

    public function sendMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chatContactId' => 'required|integer|exists:contacts,id',
            'chatAgentId'   => 'required|integer|exists:users,id',
            'content'       => 'required|string',
            'subject'       => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'  => false,
                'response' => $validator->errors()->first(), // return first validation error
                'is_admin' => false, // you may not know is_admin yet at this point
            ], 422);
        }

        $chatContactId = $request->input('chatContactId');
        $chatAgentId   = $request->input('chatAgentId');
        $mailContent   = $request->input('content');
        $mailSubject   = $request->input('subject');

        $user = User::findOrFail($chatAgentId);
        $agentWisePermission = $this->getAgentWisePermission($user);
        $isAdminUser = $agentWisePermission['isAdminUser'];

        $contact = Contact::find($chatContactId);

        if (!$contact || empty($contact->c_email)) {
            return response()->json([
                'success'  => false,
                'response' => 'Invalid contact or email not found.',
                'is_admin' => $isAdminUser,
            ], 422);
        }

        try {
            // Check SMTP configuration
            if (!$this->checkMailConfigurationUserWise($chatAgentId)) {
                return response()->json([
                    'success'  => false,
                    'response' => 'You do not have SMTP configuration. Please set it up before attempting to send emails.',
                    'is_admin' => $isAdminUser,
                ], 400);
            }

            // Apply SMTP settings
            $this->setDynamicSMTPUserWise($chatAgentId);

            $data['subject'] = $mailSubject;
            $data['content'] = $mailContent;
            $smtp_data = SmtpConfiguration::where('user_id', $chatAgentId)->first();
            if($smtp_data){
                $data['signature_image'] = $smtp_data->signature_image;
                $data['signature_text'] = $smtp_data->signature_text;
            }
            else{
                $data['signature_image'] = "";
                $data['signature_text'] = ""; 
            }
            $data['module_name'] = "contact";
            $data['contact_id'] = $chatContactId;

            Mail::to($contact->c_email)->send(new ContactMail($data));

            $this->saveEmailData($data);
            

            // Send email
            // Mail::html($mailContent, function ($message) use ($contact, $mailSubject) {
            //     $message->to($contact->c_email)
            //             ->subject($mailSubject);
            // });

            return response()->json([
                'success'  => true,
                'response' => 'Email sent successfully.',
                'is_admin' => $isAdminUser,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success'  => false,
                'response' => 'Failed to send the message. Please contact the administrator.',
                'is_admin' => $isAdminUser,
                'error'    => config('app.debug') ? $e->getMessage() : null, // only show error in debug
            ], 500);
        }
    }


    public function getTemplateData(Request $request)
    {
        $chatType = $request->input('chatType') ?? 'sms';
        $chatAgentId   = $request->input('chatAgentId');

        $is_admin = auth()->user()->can('agent-create');

        if($is_admin){
            $templateData = Template::where('template_type', $chatType)->get();
        }
        else{
            $templateData = Template::where('template_type', $chatType)
            ->where('set_for_all','yes')
            ->orWhereHas('user', function ($query) use ($chatAgentId, $chatType) {
                $query->where('template_type', $chatType)
                ->Where('user_id', $chatAgentId);
            })
            ->get();

        }

        // dd($templateData);
        foreach($templateData as $template) {
            $template->delete_permission = false;
            $is_admin = auth()->user()->can('agent-create');
            if($is_admin || ($template->set_for_all == "no" && $template->created_by == $chatAgentId)) 
                $template->delete_permission = true;
        }
        return json_encode([
            'success' => true,
            'response' => $templateData,
        ],200);
    }

    public function saveTemplate(Request $request)
    {
        $rules = [
            'template_name'   => 'required|max:100',
            'template_content'=> 'required' . ($request->template_type !== 'mail' ? '|max:200' : ''),
        ];

        if ($request->template_type === 'mail') {
            $rules['template_subject'] = 'required|max:200';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ],400);
        }

        try {
            $validated = $validator->validated();
            $chatAgentId = $request->input('chatAgentId');
            $data = $request->input();

            $templateType = $request->input('template_type') === 'mail' ? 'mail' : 'sms';
            $templateNameSlug = $this->createTemplateSlug($data);

            // Prevent duplicate template
            if (Template::where('template_name_slug', $templateNameSlug)->exists()) {
                return response()->json([
                    'success' => false,
                    'response' => 'Template already exists.'
                ]);
            }

            $templateData = Template::create([
                'template_name'      => $validated['template_name'],
                'template_name_slug' => $templateNameSlug,
                'template_content'   => $validated['template_content'],
                'template_subject'   => $templateType === 'mail' ? $validated['template_subject'] : null,
                'template_type'      => $templateType,
                'created_by'         => $chatAgentId,
            ]);

            UserTemplate::create([
                'user_id'     => $chatAgentId,
                'template_id' => $templateData->id,
            ]);

            return response()->json([
                'success' => true,
                'response' => $templateData,
                'message'  => 'New Template created successfully'
            ],200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'response' => 'Failed to create the new template. Please contact the administrator.',
                'error'    => $e->getMessage()
            ],400);
        }
    }

    public function deleteTemplate(Request $request)
    {
        $templateId = $request->templateId;
        try {
            $templateDateted = Template::where([
                'id' => $templateId
            ])->delete();
            if ($templateDateted) {

                UserTemplate::where([
                    'template_id' => $templateId
                ])->delete();

                return json_encode([
                    'success' => true,
                    'message' => 'Template Deleted Successfully'
                ],200);
            } else {
                return response()->json([
                    'success' => false,
                    'response' => "Failed to delete the template. Please contact the administrator."
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'response' => "Failed to delete the template. Please contact the administrator."
            ], 500);
        }
    }



}
