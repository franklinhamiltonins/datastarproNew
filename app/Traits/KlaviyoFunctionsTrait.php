<?php

namespace App\Traits;

use App\Jobs\AddEmailToKlaviyo;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Lead;
use DB;

trait KlaviyoFunctionsTrait
{
    public function sendKalviyo($valuEntry, $keyentry)
    {
        $contact = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'klaviyo_call_initiated', 'c_phone')
            ->where('id', $valuEntry->id)->first();
        if ($contact) {
            if (! empty($contact->c_email)) {
                $delay = $this->delayTimeCalculationKlaviyo($keyentry);

                AddEmailToKlaviyo::dispatch($contact)
                    ->delay(now()->addSeconds($delay));
                $statusUpdate = 1;
            } else {
                $statusUpdate = 2;
            }
        } else {
            $statusUpdate = 3;
        }
        $this->updateKlaviyoStatusIncontactable($valuEntry->id, $statusUpdate);

        return 0;
    }

    public function getKlaviyoHeader()
    {
        $apiKey = config('app.klaviyo_private_key');

        return [
            'Accept: application/json',
            'Revision: 2024-07-15',
            'Content-Type: application/json',
            'Authorization: Klaviyo-API-Key '.$apiKey,
        ];
    }

    public function getKlaviyoProfileCreationBody($contact_id)
    {
        $success = false;
        $payload = [];

        $contact = Contact::select('id', 'c_first_name', 'c_last_name', 'c_email', 'c_zip', 'c_phone', 'c_title',
            'c_city', 'c_county', 'c_address1', 'c_address2', 'lead_id')
            ->where('id', $contact_id)->first();
        if ($contact) {
            $leadName = Lead::where('id', $contact->lead_id)->value('name');
            if (! empty($contact->c_email)) {
                $success = true;
                $payload = [
                    'data' => [
                        'type' => 'profile',
                        'attributes' => [
                            'email' => $contact->c_email,
                            'first_name' => $contact->c_first_name,
                            'last_name' => $contact->c_last_name,
                            'organization' => ! empty($leadName) ? $leadName : '',
                            'title' => ! empty($contact->c_title) ? $contact->c_title : '',
                            'image' => '',
                            'location' => [
                                'address1' => ! empty($contact->c_address1) ? $contact->c_address1 : '',
                                'address2' => ! empty($contact->c_address2) ? $contact->c_address2 : '',
                                'city' => ! empty($contact->c_city) ? $contact->c_city : '',
                                'country' => ! empty($contact->c_county) ? $contact->c_county : '',
                                'region' => '',
                                'zip' => $contact->c_zip,
                                'timezone' => '',
                                'ip' => '127.0.0.1',
                            ],
                            'properties' => (object) [],   // Empty object
                        ],
                    ],
                ];
            }
        }

        return [
            'success' => $success,
            'payload' => $payload,
        ];
    }

    public function callKlaviyoProfileCreation($header, $payload)
    {
        $profileId = '';
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
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
            ]);

            $response = curl_exec($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            $profileId = ! empty($result['data']['id']) ? $result['data']['id'] : '';

            if (empty($profileId)) {
                $profileId = ! empty($result['errors'][0]['meta']['duplicate_profile_id']) ? $result['errors'][0]['meta']['duplicate_profile_id'] : '';
            }
        } catch (\Exception $e) {

        }

        return $profileId;
    }

    public function getKlaviyoProfileLinktolist($profileId)
    {
        // Define the array structure
        return [
            'data' => [
                [
                    'type' => 'profile',
                    'id' => $profileId,
                ],
            ],
        ];
    }

    public function callKlaviyoProfileLinktoList($header, $payload)
    {
        $linked = '';
        try {
            $curl = curl_init();

            $listId = config('app.klaviyo_list_id');

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://a.klaviyo.com/api/lists/'.$listId.'/relationships/profiles/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $header,
            ]);

            $response = curl_exec($curl);

            curl_close($curl);

        } catch (\Exception $e) {

        }

        return $linked;
    }

    public function updateKlaviyoStatusIncontactable($valuEntryId, $statusUpdate)
    {
        Contact::where('id', $valuEntryId)
            ->whereNull('email_sent_to_klaviyo')
            ->update(['email_sent_to_klaviyo' => $statusUpdate]);

        return 0;
    }
}
