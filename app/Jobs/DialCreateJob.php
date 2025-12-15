<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Model\LeadsModel\Lead;
use App\Model\Setting;
use App\Model\Dialing;
use App\Model\LeadsModel\Contact;
use App\Model\User;
use App\Traits\CommonFunctionsTrait;
use App\Traits\DialRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use App\Mail\DialcreationConfirmation;

class DialCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,CommonFunctionsTrait,DialRelatedTrait,SMTPRelatedTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 1800; 

    public $agent_list_name,$agent_id,$location_leads_id_search,$location_leads_id,$search_fields, $campaignId,$redirect_project_url,$mail_agent_id;
    public function __construct($agent_list_name,$agent_id,$location_leads_id_search,$location_leads_id,$search_fields, $campaignId,$redirect_project_url,$mail_agent_id)
    {
        $this->agent_list_name = $agent_list_name;
        $this->agent_id = $agent_id;
        $this->location_leads_id_search = $location_leads_id_search;
        $this->location_leads_id = $location_leads_id;
        $this->search_fields = $search_fields;
        $this->campaignId = $campaignId;
        $this->redirect_project_url = $redirect_project_url;
        $this->mail_agent_id = $mail_agent_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // DB::beginTransaction();
        try {
            $checkdialing = Dialing::where('name',$this->agent_list_name)->first();
            if(!$checkdialing){
                $dialing = new Dialing();
                $dialing->name = $this->agent_list_name;
                $dialing->lead_number = 0;
                // $dialing->name = $this->agent_list_name;
                $dialing->save();


                $agent_arr = [];
                foreach ($this->agent_id as $keyagent => $single_agent_id) {
                    $agent_arr[$keyagent] = $single_agent_id;
                    $user = User::find($single_agent_id);
                    if($user){
                        $user->dialings()->attach($dialing->id);
                    }
                }

                $leadsQuery = $this->leadoutputget_fordialing($this->location_leads_id_search,$this->location_leads_id,$this->search_fields, $this->campaignId);

                $leadsQuery->groupBy('leads.id')->chunk(1000, function($leads)use ($agent_arr, $dialing){
                    $insert_array = [];
                    $key = 0;
                    foreach ($leads as $lead) {
                        $agentIndex = $key % count($agent_arr);
                        $agentValue = $agent_arr[$agentIndex];

                        $insert_array[] = [
                            'dialing_id' => $dialing->id,
                            'lead_id' => $lead->id,
                            'owned_by_agent_id' => 0,
                            'status' => 'free',
                            'assigned_to_agent_id' => $agentValue
                        ];

                        // DB::table('dialings_leads')
                        // ->updateOrInsert(
                        //     ['dialing_id' => $dialing->id, 'lead_id' => $lead->id],
                        //     ['owned_by_agent_id' => 0, 'status' => 'free', 'assigned_to_agent_id' => $agentValue]
                        // );
                        $key++;
                    }
                    DB::table('dialings_leads')
                    ->Insert($insert_array);
                    unset($insert_array);
                });
                // unset($agent_arr);

                $setting_time_data = Setting::select('notify_email')->first();

                if($setting_time_data && !empty($setting_time_data->notify_email)){
                    $recipientEmail_arr = explode(',', $setting_time_data->notify_email);

                    $recipientEmail = $recipientEmail_arr[0];

                    $ccEmails = array_slice($recipientEmail_arr, 1);
                    $data = [
                        'message' => 'Your dialing creation (' . $dialing->name . ') has been confirmed. Please check this URL: ' . $this->redirect_project_url
                    ];

                    $this->setDynamicSMTPUserWise($this->mail_agent_id);

                    $mail = Mail::to($recipientEmail);

                    if (count($ccEmails) > 0) {
                        $mail->cc($ccEmails);
                    }

                    $mail->send(new DialCreationConfirmation($data));

                    // Mail::to($recipientEmail)->send(new DialCreationConfirmation($data));
                    unset($recipientEmail,$data,$recipientEmail_arr,$ccEmails);
                }
                unset($setting_time_data);
                DB::disconnect();
            }
            // DB::commit();
        } catch (\Exception $e) {
            // DB::rollBack();
            // throw $e; // Re-throw the exception so Laravel handles retries if needed.
        }
    }
}
