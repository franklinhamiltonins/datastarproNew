<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Model\SmsProvider;
use App\Model\SmsProviderQueue;
use Redirect, Response;
use App\Model\LeadsModel\Contact;
use Carbon\Carbon;

class SendSmsContactUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $smsprovirderdata;
    public $updateFlag;
    // public $lastSmsProviderId;
    // public $updateFlagValue;
    public function __construct(SmsProvider $smsprovirderdata, $updateFlag = '')
    {
        $this->smsprovirderdata = $smsprovirderdata;
        $this->updateFlag = $updateFlag;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $smsprovider = $this->smsprovirderdata; 
        $updateFlagValue = $this->updateFlag; 
        
        // new delay details
        $newDayDelay = $smsprovider->day_delay;
        $newMinuteDelay = $smsprovider->minute_delay;

        // Calculate the new total delay in minutes
        $newTotalDelayMinutes = ($newDayDelay * 24 * 60) + $newMinuteDelay;
    
        // Get the id of the new SmsProvider
        $smsProviderId = $smsprovider->id;

        // checking max id to make flag 1 in contact 
        $lastSmsProviderId = SmsProvider::
                            select('id')
                            // ->selectRaw('(day_delay * 24 * 60) + minute_delay AS total_delay_minutes')
                            // ->selectRaw('COALESCE(day_delay, 0) * 24 * 60 + COALESCE(minute_delay, 0) AS total_delay_minutes')
                            ->orderBy('day_delay', 'desc')
                            ->orderBy('minute_delay', 'desc')
                            // ->orderBy('day_delay', 'desc')
                            // ->orderBy('minute_delay', 'desc')
                            ->limit(1)
                            ->pluck('id')
                            ->first();
    
        // Retrieve contacts that meet the criteria
        $contacts = Contact::select('id', 'current_sent_smsprovider_id', 'first_sms_date_time', 'next_sms_date_time', 'skip_response_step', 'respond_to_cron_flag')
        ->where(function($query) {
            $query->where(function($subQuery) {
                $subQuery->whereNotNull('c_phone')
                     ->where('c_phone', '<>', '');
            })->orWhere(function($subQuery) {
                $subQuery->whereNotNull('c_email')
                     ->where('c_email', '<>', '');
            });
        })
        // ->whereNotNull('c_phone')
        ->whereNotNull('first_sms_date_time')
        ->whereNotNull('next_sms_date_time')
        // ->where('next_sms_date_time','<', date("Y-m-d H:i:s", strtotime('+'.$newTotalDelayMinutes.' minutes')))
        ->chunk(500,function($contacts) use($newDayDelay,$newMinuteDelay,$newTotalDelayMinutes,$smsProviderId,$smsprovider, $lastSmsProviderId,$updateFlagValue){
            $currentTime = Carbon::now();

            // echo " //start2--". $lastSmsProviderId. ' //smsProviderId--'. $smsProviderId;
            // echo "updateFlagValue- ".$updateFlagValue;

            foreach ($contacts as $contact) {
                $firstDateTime = Carbon::parse($contact->first_sms_date_time);
                $nextDateTime = Carbon::parse($contact->next_sms_date_time);

                // previous sms_provider_id in contact table
                $prevSmsProviderId = $contact->current_sent_smsprovider_id;

                // Calculate the total delay date time
                $totalDelayDateTime = $firstDateTime->copy()->addMinutes($newTotalDelayMinutes);
        
                if ($totalDelayDateTime > $nextDateTime) {
                    if($totalDelayDateTime > $currentTime && $contact->respond_to_cron_flag == 1){
                        $contact->update([
                            //'respond_to_cron_flag' => 0
                            // 'respond_to_cron_flag' => $updateFlagValue == 'delay_update' && $lastSmsProviderId == $smsProviderId ? 1 : 0,
                            'respond_to_cron_flag' => 0,
                            'next_sms_date_time' => $totalDelayDateTime,
                        ]);
                    }
                    // echo 1;
                    // Case 1: Update respond_to_cron_flag if totalDelayDateTime is greater than next_sms_date_time
                    
                } else if ($totalDelayDateTime > $currentTime && $totalDelayDateTime < $nextDateTime) {
                    // echo 2;
                    // Case 2: Update respond_to_cron_flag, next_sms_date_time, and current_sent_smsprovider_id
                    $contact->update([
                        // 'respond_to_cron_flag' => 0,
                        'respond_to_cron_flag' => 0,
                        'next_sms_date_time' => $totalDelayDateTime,
                        // 'current_sent_smsprovider_id' => $smsProviderId,
                    ]);

                    // update sms_provider_queue
                    // $affectedRows = SmsProviderQueue::where([
                    //     'contact_id' => $contact->id,
                    //     'sms_provider_id' => $prevSmsProviderId,
                    //     'sms_sent_flag' => 0
                    // ])->update([
                    //     'sms_provider_id' => $smsProviderId,
                    //     'day_delay' => $newDayDelay
                    // ]);
            
                    // if ($affectedRows > 0) {
                        // echo "Rows updated: {$affectedRows} for contact_id: {$contact->id} with prevSmsProviderId: {$prevSmsProviderId}\n";
                    // } else {
                        // echo "No rows updated for contact_id: {$contact->id} with prevSmsProviderId: {$prevSmsProviderId}\n";
                    // }
                                        
                } else if ($totalDelayDateTime < $currentTime) {
                    // echo 3;
                    // Case 3: Do nothing if totalDelayDateTime is in the past
                    continue;
                }
            }
        });    
            
    }
}
