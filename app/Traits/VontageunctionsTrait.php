<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\SmtpConfiguration;
use Illuminate\Support\Facades\Crypt;
use App\Model\Email;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Vonage\Client;
use Illuminate\Support\Facades\Log;
use App\Model\SmsProviderQueue;
use App\Model\SmsProvider;
use DB;
use Carbon\Carbon;
use App\Jobs\SendSmsVontageThroughQueue;
use App\Model\Message;

trait VontageunctionsTrait
{
    public function sendvonagesms_fromqueue($valuentry,$keyentry)
    {
        $contact = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip','c_phone')
        ->where('id',$valuentry->contact_id)->first();
        if($contact){
            if(!empty($contact->c_phone)){
                $delay = $this->delaytimecalculation($keyentry);

                // $request_data = [
                //     'sms_provider_id' => $valuentry->sms_provider_id,
                //     // 'sms_content' => SmsProvider::where('id',$valuentry->sms_provider_id)->value('text'),
                //     'sms_content' => SmsProvider::where('id',$valuentry->sms_provider_id)->value('text')." - ".$contact->c_phone,
                //     'contact_id' => $contact->id,
                //     // 'c_phone' => $contact->c_phone,
                //     'c_phone' => '9546109418',
                // ];

                $request_data = $this->Vontage_queue_request_data($valuentry->sms_provider_id,$contact->c_phone,$contact->id);

                SendSmsVontageThroughQueue::dispatch($request_data)
                ->delay(now()->addSeconds($delay));
                $status_update = 1;
            }
            else{
                $status_update = 2;
            }
        }
        else{
            $status_update = 3;
        }
        $this->updatesmssentflag_insmsproviderqueue($valuentry->id,$status_update);

        return 0;
    }

    public function Vontage_queue_request_data($sms_provider_id,$c_phone,$contact_id)
    {
        $sms_content = $this->vontagesms_content($sms_provider_id,$contact_id);
        return [
            'sms_provider_id' => $sms_provider_id,
            // 'sms_content' => SmsProvider::where('id',$sms_provider_id)->value('text'),
            // 'sms_content' => $sms_content." - ".$c_phone,
            'sms_content' => $sms_content,
            'contact_id' => $contact_id,
            'c_phone' => $c_phone,
            // 'c_phone' => '9546109418',
        ];
    }

    public function vontagesms_content($sms_provider_id,$contact_id)
    {
        $precontent = SmsProvider::where('id',$sms_provider_id)->value('text');

        $precontent = strip_tags($precontent);
        $precontent = html_entity_decode($precontent);

        // Remove any unnecessary spaces
        $precontent = trim($precontent);

        $contact = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip','c_phone','lead_id')
        ->where('id',$contact_id)->first();

        if($contact){
            $replacements = [
                '{CANDIDATE_FIRST_NAME}' => $contact->c_first_name,
                '{CANDIDATE_LAST_NAME}'  => $contact->c_last_name,
                '{BUSINESS_NAME}'        => Lead::where('id', $contact->lead_id)->value('name')
            ];

            return $this->returnPrepareString($precontent, $replacements);
        }
        return '';

        
    }

    public function returnPrepareString($textstr, $replacements) {
        // Replace placeholders with corresponding values
        return str_replace(array_keys($replacements), array_values($replacements), $textstr);
    }

    public function sendVontagesms($tomobile,$content)
    {
        $return_type = 0;
        try {
            $vonage_key = env('VONAGE_KEY');
            $vonage_secret = env('VONAGE_SECRET');
            $vonage_from = env('VONAGE_FROM');
            $vonage_from = '+18882024249';

            $vonage_new_client = new Client(new \Vonage\Client\Credentials\Basic($vonage_key, $vonage_secret));
            
            $vonageResponse = $vonage_new_client->message()->send([
                'to' => '+1 ' . $tomobile,
                'from' => $vonage_from,
                'text' => $content,
            ]);

            $message = $vonageResponse->current();

            if (isset($message['status']) &&  $message['status'] == 0) {
                $chat_sms_sent_status = 1;
                $res = json_encode($message);
            } else {
                $chat_sms_sent_status = 2;
                $res = '';
            }
        } catch (\Exception $e) {
            $chat_sms_sent_status = 3;
            $res = '';
            Log::error('Exception occurred at line ' . $e->getLine() . ' : ' . $e->getMessage());
        }
        return [
            'chat_sms_sent_status' => $chat_sms_sent_status,
            'res' => $res,
        ];
    }

    public function updatesmssentflag_insmsproviderqueue($valuentry_id,$status_update)
    {
        SmsProviderQueue::where('id',$valuentry_id)
        ->where('sms_sent_flag',0)
        ->update(['sms_sent_flag'=>$status_update]);

        return 0;
    }

    public function outboundsavemessage($request_data,$response)
    {
        $message = new Message();
        $message->user_id = 0;
        $message->contact_id = $request_data['contact_id'];
        $message->max_time_to_send = Carbon::now();
        $message->through_sms_provider_flag = 1;
        $message->content = $request_data['sms_content'];
        $message->chat_sms_sent_status = $response['chat_sms_sent_status'];
        $message->vonageResponse = $response['res'];
        $message->chat_type = 'outbound'; // Change as needed
        $message->save();
    }

    
}
