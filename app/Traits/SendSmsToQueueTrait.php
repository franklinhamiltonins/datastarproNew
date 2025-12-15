<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Model\LeadsModel\Contact;
use App\Model\SmsProviderQueue;
use App\Model\SmsProvider;
use Carbon\Carbon;
use App\Jobs\AddEmailToKlaviyo;
use App\Jobs\SendSmsVontageThroughQueue;


//before using this trait , VontageunctionsTrait trait also need to use
trait SendSmsToQueueTrait
{
    public function nextsmsprovider($contact,$isFirstTime)
    {
        if($isFirstTime){
            $skip = 1;
        }
        else{
            $skip = ($contact->skip_response_step + 1);
        }

        $next_sms_provider = SmsProvider::select('*')
                    ->orderBy('day_delay', 'asc')
                    ->orderBy('minute_delay', 'asc')
                    ->offset($skip)
                    ->limit(1)
                    ->first();

        return $next_sms_provider;
    }

    /**
    * Get the appropriate SMS Provider based on the contact state.
    */
    public function getSmsProvider($contact, $isFirstTime) {
        $smsProvider = [];

        if ($isFirstTime) {
            // echo " //isFirstTime ---> ".$isFirstTime; 
            $smsProvider= SmsProvider::select('*')
                        ->orderBy('day_delay', 'asc')
                        ->orderBy('minute_delay', 'asc')
                        ->limit(1)
                        ->first();
            // echo "if smsProvider ---> ".$smsProvider->id;
        } else {
            // echo 'contact skip ' . $contact->skip_response_step;
            $smsProvider = SmsProvider::select('*')
                ->orderBy('day_delay', 'asc')
                ->orderBy('minute_delay', 'asc')
                ->skip($contact->skip_response_step)
                ->limit(1)
                ->first();
            
           // echo "if ELSE smsProvider ---> ".$smsProvider->id;
        }
        return $smsProvider;
    }

    /**
     * Calculate the delay date time based on SMS Provider settings.
     */
    public function calculateDelayDateTime($startDate, $smsProvider) {
        return $smsProvider->minute_delay == 0 
            ? $startDate->addDays($smsProvider->day_delay) 
            : $startDate->addDays($smsProvider->day_delay)->addMinutes($smsProvider->minute_delay);
    }

    public function sendcontactwisesmsproviderandklaviyo($contact,$isFirstTime=true)
    {
    	if(!empty($contact->c_email) || !empty($contact->c_phone)){
			$min_smsprovider_id = SmsProvider::orderBy('day_delay', 'ASC')->orderBy('minute_delay', 'ASC')->value('id');

			$this->updatecontacttable_fornextsmsprovider($min_smsprovider_id,$contact,$isFirstTime,0,0);

			// addition to klaviyo
			if(!empty($contact->c_email)){
				$this->sendemailtoklaviyoinside($contact);
			}
			// send first vontage message
			if(!empty($contact->c_phone)){
				$this->sendvontagemessageinsidefirst($min_smsprovider_id,$contact);
			}
		}
    }

    public function updatecontacttable_fornextsmsprovider($current_sent_smsprovider_id,$contact,$isFirstTime,$makeloginqueue,$day_delay)
    {
    	$startDate = $isFirstTime ? Carbon::now() : Carbon::parse($contact->first_sms_date_time);
		$next_sms_provider = $this->nextsmsprovider($contact,$isFirstTime);
        if($next_sms_provider){
            $delayDateTime = $this->calculateDelayDateTime($startDate, $next_sms_provider);
            $respond_to_cron_flag = 0;
        }
        else{
            $delayDateTime = $contact->next_sms_date_time;
            $respond_to_cron_flag = 1;
        }
        Contact::where('id',$contact->id)
        ->update([
            // 'current_sent_smsprovider_id' => $isFirstTime ? 2 : $smsProvider->id + 1,
            'current_sent_smsprovider_id' => $current_sent_smsprovider_id,
            'first_sms_date_time' => $isFirstTime ? Carbon::now() : $contact->first_sms_date_time,
            // 'last_sms_date_time' => Carbon::now() ,
            'next_sms_date_time' => $delayDateTime,
            'respond_to_cron_flag' => $respond_to_cron_flag,
            'skip_response_step' => $isFirstTime ? 1 : $contact->skip_response_step + 1
        ]);

        if($makeloginqueue == 1){
        	SmsProviderQueue::create([
	            'contact_id' => $contact->id,
	            'sms_sent_flag' => 0,
	            'sms_provider_id' => $current_sent_smsprovider_id,
	            'day_delay' => $day_delay
	        ]);
        }
    }

    public function sendemailtoklaviyoinside($contact)
    {
        AddEmailToKlaviyo::dispatch($contact);
        $this->updateklaviyostatus_incontactable($contact->id,1);
    }

    public function sendvontagemessageinsidefirst($min_smsprovider_id,$contact)
    {
        $request_data = $this->Vontage_queue_request_data($min_smsprovider_id,$contact->c_phone,$contact->id);

        SendSmsVontageThroughQueue::dispatch($request_data);
    }

    public function updatecontactinfomation_basedklaviyovontage($oldcontact,$contact,$isFirstTime=true)
    {
        $min_smsprovider_id = SmsProvider::orderBy('day_delay', 'ASC')->orderBy('minute_delay', 'ASC')->value('id');

        if(empty($oldcontact->c_email) && empty($oldcontact->c_phone)){
            if(!empty($contact->c_email) || !empty($contact->c_phone)){
                $this->updatecontacttable_fornextsmsprovider($min_smsprovider_id,$contact,$isFirstTime,0,0);
            }
        }

        // addition to klaviyo
        if(empty($oldcontact->c_email) && !empty($contact->c_email)){
            $this->sendemailtoklaviyoinside($contact);
        }
        // send first vontage message
        if(empty($oldcontact->c_phone) && !empty($contact->c_phone)){
            $this->sendvontagemessageinsidefirst($min_smsprovider_id,$contact);
        }
    }

    public function promotional_msg_sending_check()
    {
        $estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));

        // Output the formatted time
        $hourest = $estTime->format('H');

        if($hourest >= 9 && $hourest < 19){
            return true;
        }
        else{
            return false;
        }
    }
}
