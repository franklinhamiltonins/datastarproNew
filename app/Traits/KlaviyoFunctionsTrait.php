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
use App\Model\SmsProviderQueue;
use App\Model\SmsProvider;
use DB;
use App\Jobs\AddEmailToKlaviyo;

trait KlaviyoFunctionsTrait
{
    public function sendkalviyo($valuentry,$keyentry)
    {
        $contact = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated','c_phone')
        ->where('id',$valuentry->id)->first();
        if($contact){
            // $contact->c_email = 'test'.rand(100,500).date('h_i_s').'@testing.com';
            
            if(!empty($contact->c_email)){
                $delay = $this->delaytimecalculationklaviyo($keyentry);
                // dd($contact);

                // DB::table('sms_provider_queue_log')
                // ->insert([
                //     'contact_id' => $contact->id,
                //     'c_email' => $contact->c_email,
                //     'delay' => $delay,
                //     'date' => date('Y-m-d'),
                //     'time' => date('H:i:s'),
                //     'activity' => 'dispatch',
                // ]);
                AddEmailToKlaviyo::dispatch($contact)
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
        $this->updateklaviyostatus_incontactable($valuentry->id,$status_update);

        return 0;
    }

    public function getklaviyoheader()
    {
        $apiKey = config('app.klaviyo_private_key');
        return array(
            'Accept: application/json',
            'Revision: 2024-07-15',
            'Content-Type: application/json',
            'Authorization: Klaviyo-API-Key '.$apiKey
        );
    }

    public function getklaviyoprofilecreation_body($contact_id)
    {
        $success = false;
        $payload = [];

        $contact = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip','c_phone','c_title','c_city','c_county','c_address1','c_address2','lead_id')
        ->where('id',$contact_id)->first();
        if($contact){
            $lead_name = Lead::where('id', $contact->lead_id)->value('name');
            // if(!empty($contact->c_phone) && !empty($contact->c_email)){
            if(!empty($contact->c_email)){
                $success = true;
                $payload = [
                    "data" => [
                        "type" => "profile",
                        "attributes" => [
                            "email" => $contact->c_email,
                            // "phone_number" => "+".$contact->c_phone,
                            "first_name" => $contact->c_first_name,
                            "last_name" => $contact->c_last_name,
                            "organization" => !empty($lead_name )?$lead_name :'',
                            "title" => !empty($contact->c_title) ? $contact->c_title : '',
                            "image" => "",
                            "location" => [
                                "address1" => !empty($contact->c_address1) ? $contact->c_address1 : '',
                                "address2" => !empty($contact->c_address2) ? $contact->c_address2 : '',
                                "city" => !empty($contact->c_city) ? $contact->c_city : '',
                                "country" => !empty($contact->c_county) ? $contact->c_county : '',
                                "region" => "",
                                "zip" => $contact->c_zip,
                                "timezone" => "",
                                "ip" => "127.0.0.1"
                            ],
                            "properties" => (object) []   // Empty object
                        ]
                    ]
                ];
            }
        }

        return [
            'success' => $success,
            'payload' => $payload,
        ];
    }

    public function callklaviyoprofile_creation($header,$payload)
    {
        $profile_id = '';
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://a.klaviyo.com/api/profiles/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);

            curl_close($curl);
        
            
            $result = json_decode($response, true);

            $profile_id =  !empty($result['data']['id'])?$result['data']['id']:'';

            if(empty($profile_id)){
                $profile_id =  !empty($result['errors'][0]['meta']['duplicate_profile_id'])?$result['errors'][0]['meta']['duplicate_profile_id']:'';
            }
        }
        catch (\Exception $e) {
            
        }


        return $profile_id;
    }

    public function getklaviyoprofile_linktolist($profile_id)
    {
        // Define the array structure
        return [
            "data" => [
                [
                    "type" => "profile",
                    "id" => $profile_id
                ]
            ]
        ];
    }

    public function callklaviyoprofile_linktolist($header,$payload)
    {
        $linked = '';
        try {
            $curl = curl_init();

            $list_id = config('app.klaviyo_list_id');

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://a.klaviyo.com/api/lists/'.$list_id.'/relationships/profiles/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($payload),
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            
        }
        catch (\Exception $e) {
            
        }


        return $linked;
    }

    public function updateklaviyostatus_incontactable($valuentry_id,$status_update)
    {
        Contact::where('id',$valuentry_id)
        ->whereNull('email_sent_to_klaviyo')
        ->update(['email_sent_to_klaviyo'=>$status_update]);

        return 0;
    }
}
