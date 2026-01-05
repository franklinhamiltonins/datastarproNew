<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Model\LeadsModel\Lead;
use App\Model\Dialing;
use App\Model\Agentlistlead;
use App\Model\LeadsModel\Contact;
use App\Model\User;
use App\Model\Calllog;
use App\Model\Agentlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Model\Setting;
use App\Traits\CommonFunctionsTrait;
use App\Traits\DialRelatedTrait;
use App\Traits\SMTPRelatedTrait;
use App\Traits\MailingRelatedTrait;

class AgentReassignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,CommonFunctionsTrait,DialRelatedTrait,SMTPRelatedTrait,MailingRelatedTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $selected_agent_list_ids,$selected_agent_id,$redirect_project_url,$mail_agent_id;
    public function __construct($selected_agent_list_ids,$selected_agent_id,$redirect_project_url,$mail_agent_id)
    {
        $this->selected_agent_list_ids = $selected_agent_list_ids;
        $this->selected_agent_id = $selected_agent_id;
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
        $selected_agent_id = $this->selected_agent_id;
        foreach ($this->selected_agent_list_ids as $agent_list_id) {

            DB::table('dialings_leads')->where('dialing_id', $agent_list_id)->where('status', 'free')
            ->orderBy('lead_id')
            ->chunk(2000, function($freeLeads)use ($selected_agent_id, $agent_list_id){
                $key = 0;
                $agentlist = [];
                foreach ($freeLeads as $free_lead) {
                    $agentIndex = $key % count($selected_agent_id);
                    $agentValue = $selected_agent_id[$agentIndex];
                    $agentlist[$agentValue][] = $free_lead->lead_id;
                    $key++;
                }
                foreach ($agentlist as $keyagent => $valueagent) {
                    DB::table('dialings_leads')->whereIn('lead_id',$valueagent)
                    ->where('dialing_id',$agent_list_id)
                    ->update(['owned_by_agent_id' => 0,  'assigned_to_agent_id' => $keyagent]);
                }
                unset($agentlist);
            });

            foreach ($selected_agent_id as $agent_id) {
                DB::table('dialing_user')
                    ->updateOrInsert(
                        ['user_id' => $agent_id, 'dialing_id' => $agent_list_id],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
            }
            DB::table('dialing_user')
                ->where('dialing_id', $agent_list_id)
                ->whereNotIN('user_id', $selected_agent_id)
                ->delete();

            // try {
                $setting_time_data = Setting::select('notify_email')->first();
                if($setting_time_data && !empty($setting_time_data->notify_email)){
                    $recipientEmail_arr = explode(',', $setting_time_data->notify_email);

                    $recipientEmail = $recipientEmail_arr[0];

                    $ccEmails = array_slice($recipientEmail_arr, 1);

                    $this->setDynamicSMTPUserWise($this->mail_agent_id);

                    $dialing = Dialing::find($agent_list_id);
                    
                    Mail::send([], [], function ($message) use ($recipientEmail,$ccEmails,$dialing) {
                        $message->to($recipientEmail);

                        if (count($ccEmails) > 0) {
                            $message->cc($ccEmails);
                        }
                        $message->subject('Dialing Agent Reassignment')
                        ->html("Your dialing  ('" . $dialing->name . "') agent reassignment has been confirmed. Please check this URL: " . $this->redirect_project_url);
                    });
                }
                unset($setting_time_data);
                
            // } catch (\Throwable $th) {
                
            // }

        }
        DB::disconnect();
    }
}
