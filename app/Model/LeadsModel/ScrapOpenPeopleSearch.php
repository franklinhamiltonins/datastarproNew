<?php

namespace App\Model\LeadsModel;

use DateTime;
use Error;
use stdClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;



class ScrapOpenPeopleSearch extends Model
{
    public static function callForOpenPeopleSearchApi($contacts, $api_setting_data)
    {

        try {
            $contacts_updated_arr = [];
            $contacts_skipped_arr = [];
            $store_contact_api_platform = [];
            $store_social_profile_data = [];
            $contacts_updated_count = 0;
            $contacts_skipped_count = 0;
            $api_auth_token = '';
            $skipped_prospect_status = 'not found';
            $partial_prospect_status = 'not found';
            $prospect_merged_status = 'processed_next';

            $scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($api_setting_data->id);
            // dd(count($scrap_platform_settings));
            if (count($scrap_platform_settings) > 0) {
                $skipped_prospect_status = 'unavailable';
                $prospect_merged_status = 'pending';
            }
            if (count($scrap_platform_settings) > 0) {
                $partial_prospect_status = 'unavailable';
            }

            if ($api_setting_data->auth_token_required == 1) {
                if ($api_setting_data->api_auth_token != '' && date($api_setting_data->auth_expiry_date) > new DateTime('now')) {
                    $api_auth_token = $api_setting_data->api_auth_token;
                } else {
                    /*** auth expired and call for auth api ***/
                    $username_and_key['username'] =  $api_setting_data->api_username;
                    $username_and_key['password'] =  $api_setting_data->api_key;

                    $api_auth_token = self::callOpenPeopleAuthentication($api_setting_data->api_auth_url, json_encode($username_and_key), $api_setting_data->id);
                }
                foreach ($contacts as $key => $val) {
                    // dd($api_auth_token);
                    $call_open_people_search_arr = self::callOpenPeopleSearch($val, $api_auth_token, $api_setting_data->api_contact_search_url);
                    // dd($call_open_people_search_arr);
                    if ($call_open_people_search_arr['status']) {
                        $contact_scrap_to_be_updated = self::formatContactScrapUpdate($call_open_people_search_arr['data'], $val, $partial_prospect_status, $api_setting_data->id);
                        // dd($val['id'], $contact_scrap_to_be_updated);
                        Contact::updateContactsWithScrapData($contact_scrap_to_be_updated['contact_scrap_to_be_updated'], $val['id']);

                        $store_contact_api_platform[] = array('contact_id' => $val['id'], 'status' => $contact_scrap_to_be_updated['status'], 'api_platform_id' => $api_setting_data['id'], 'record_name' => $api_setting_data['platform_name'] . '_' . date('y-m-d'), 'merged_status' => $prospect_merged_status, 'created_at' => date('y-m-d H:i:s'));
                        $contacts_updated_count++;
                    } else {
                        $prev_status = Contact::checkForCurrentStatus($val['id'], $api_setting_data['id']);
                        $status = ($prev_status == 'partial') ? $prev_status : $skipped_prospect_status;
                        // $status = Contact::checkForCurrentStatus($val['id'], $api_setting_data['id']);
                        $store_contact_api_platform[] = array('contact_id' => $val['id'], 'status' => $status, 'api_platform_id' => $api_setting_data['id'], 'record_name' => $api_setting_data['platform_name'] . '_' . date('y-m-d'), 'merged_status' => $prospect_merged_status, 'created_at' => date('y-m-d H:i:s'));
                        $contacts_skipped_count++;
                    }
                    $contacts_updated_arr['contacts_used'][] = $val['c_first_name'];
                }
                //call store Contact Api Platform Status
                Contact::storeContactApiPlatformStatus($store_contact_api_platform);

                $contacts_updated_arr['contacts_updated_count'] = $contacts_updated_count;
                $contacts_updated_arr['contacts_skipped_count'] = $contacts_skipped_count;


                return ['status' => 'true', 'data' => $contacts_updated_arr, 'message' => 'Records updated.'];
            } else
                throw new Error('Something unexpected happened!');
        } catch (\Throwable $err) {
            toastr()->error($err);
            Log::channel('callForOpenPeopleSearchApi')->info('Scrap status of ' . $err);
            throw ($err);
        }
    }
    //Open People search curl
    public function callOpenPeopleSearch($contacts, $auth_token, $curl_url)
    {

        $contact['firstName'] = $contacts['c_first_name'];
        $contact['lastName'] = $contacts['c_last_name'];
        $contact['state'] = $contacts['c_state'];
        $contact['city'] = $contacts['c_city'];
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($contact),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $auth_token
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response, true);
            if ($response != null || !empty($response))
                return ['status' => true, 'data' => $response['results']];
            else
                return ['status' => false, 'data' => 'No data found'];
        } catch (\Throwable $err) {
            Log::channel('callForOpenPeopleSearchApi')->info('Scrap status of ' . $err);
            toastr()->error($err);
            throw ($err);
        }
    }
    //call OpenPeople Authentication
    public function callOpenPeopleAuthentication($auth_url, $username_and_key, $api_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $auth_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $username_and_key,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        $Lead = ScrapApiPlatform::where('id', $api_id)
            ->update([
                'api_auth_token' => $response['token'],
                'auth_expiry_date' =>  Date($response['token_expiry_utc']),
            ]);
        return $response['token'];
    }

    // public function formatContactScrapUpdate($data, $contact_data, $partial_prospect_status, $api_setting_id)
    // {
    //     $response = [];
    //     $prev_status = Contact::checkForCurrentStatus($contact_data['id'], $api_setting_id);

    //     $status = ($prev_status == 'unavailable') ? $partial_prospect_status : $prev_status;
    //     $contact_scrap_to_be_updated = array();
    //     $final_contact_arr = count($data) > 3 ? self::filterScrapData($data) : $data;
    //     if (count($data) > 0) {
    //         foreach ($final_contact_arr as $index => $val) {
    //             if ($val['phone'] != null || $val['phone'] != '') {
    //                 $status = 'success';
    //                 if (!isset($contact_scrap_to_be_updated['c_phone']) ||  $contact_scrap_to_be_updated['c_phone'] == '')
    //                     $contact_scrap_to_be_updated['c_phone'] = $val['phone'];
    //                 elseif (!isset($contact_scrap_to_be_updated['c_secondary_phone']) ||  $contact_scrap_to_be_updated['c_secondary_phone'] == '')
    //                     $contact_scrap_to_be_updated['c_secondary_phone'] = $val['phone'];
    //             } else
    //                 $status = $partial_prospect_status;

    //             if ($val['email'] != null || $val['email'] != '') {
    //                 // $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
    //                 if (!isset($contact_scrap_to_be_updated['c_email']) ||  $contact_scrap_to_be_updated['c_email'] == '')
    //                     $contact_scrap_to_be_updated['c_email'] = $val['email'];
    //                 elseif (!isset($contact_scrap_to_be_updated['c_secondary_email']) ||  $contact_scrap_to_be_updated['c_secondary_email'] == '')
    //                     $contact_scrap_to_be_updated['c_secondary_email'] = $val['email'];
    //             } else
    //                 $status = $partial_prospect_status;

    //             if ($val['address'] != null) {
    //                 $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
    //                 $contact_scrap_to_be_updated['c_address1'] = $val['address'];
    //             }
    //             if ($val['city'] != null) {
    //                 $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
    //                 $contact_scrap_to_be_updated['c_city'] = $val['city'];
    //             }
    //             if ($val['zip'] != null) {
    //                 $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
    //                 $contact_scrap_to_be_updated['c_zip'] = $val['zip'];
    //             }
    //         }
    //     }
    //     $contact_scrap_to_be_updated['prospect_verified'] = $status;
    //     $response['status'] = $status;
    //     $response['contact_scrap_to_be_updated'] = $contact_scrap_to_be_updated;

    //     return $response;
    // }

    public function formatContactScrapUpdate($data, $contact_data, $partial_prospect_status, $api_setting_id)
    {
        // dd($api_setting_id);
        $response = [];

        $status = $partial_prospect_status;
        $contact_scrap_to_be_updated = array();
        $final_contact_arr = count($data) > 3 ? self::filterScrapData($data) : $data;
        // dd($final_contact_arr);
        if (count($final_contact_arr) > 0) {
            foreach ($final_contact_arr as $index => $val) {
                if (($val['phone'] != null || $val['phone'] != '') && $val['address'] != null && $val['city'] != null && $val['zip'] != null) {
                    $status = 'success';
                    // $contact_scrap_to_be_updated['is_updated'] = 1;
                    if (!isset($contact_data['c_phone']) ||  $contact_data['c_phone'] == '')
                        $contact_scrap_to_be_updated['c_phone'] = $val['phone'];
                    if (!isset($contact_data['c_secondary_phone']) ||  $contact_data['c_secondary_phone'] == '')
                        $contact_scrap_to_be_updated['c_secondary_phone'] = $val['phone'];
                    if ($contact_data['c_address1'] == '')
                        $contact_scrap_to_be_updated['c_address1'] = $val['address'];
                    if ($contact_data['c_city'] == '')
                        $contact_scrap_to_be_updated['c_city'] = $val['city'];;
                    if ($contact_data['c_zip'] == '')
                        $contact_scrap_to_be_updated['c_zip'] = $val['zip'];
                } elseif ($val['phone'] != null || $val['phone'] != '' || $val['address'] != null || $val['city'] != null || $val['zip'] != null) {
                    $status = 'partial';
                    // $contact_scrap_to_be_updated['is_updated'] = 1;
                    if (!isset($contact_data['c_phone']) ||  $contact_data['c_phone'] == '')
                        $contact_scrap_to_be_updated['c_phone'] = $val['phone'];
                    if (!isset($contact_data['c_secondary_phone']) ||  $contact_data['c_secondary_phone'] == '')
                        $contact_scrap_to_be_updated['c_secondary_phone'] = $val['phone'];
                    if ($contact_data['c_address1'] == '')
                        $contact_scrap_to_be_updated['c_address1'] = $val['address'];
                    if ($contact_data['c_city'] == '')
                        $contact_scrap_to_be_updated['c_city'] = $val['city'];;
                    if ($contact_data['c_zip'] == '')
                        $contact_scrap_to_be_updated['c_zip'] = $val['zip'];
                } elseif (($val['phone'] == '') && $val['address'] == null && $val['city'] == null && $val['zip'] == null) {
                    $prev_status = Contact::checkForCurrentStatus($contact_data['id'], $api_setting_id);
                    $status = ($prev_status == 'partial') ? $prev_status : $partial_prospect_status;
                }

                if (!isset($contact_data['c_email']) ||  $contact_data['c_email'] == '')
                    $contact_scrap_to_be_updated['c_email'] = ($val['email'] != null || $val['email'] != '') ? $val['email'] : '';
                elseif (!isset($contact_data['c_secondary_email']) ||  $contact_data['c_secondary_email'] == '')
                    $contact_scrap_to_be_updated['c_secondary_email'] = $val['email'];
            }
        } else {
            $prev_status = Contact::checkForCurrentStatus($contact_data['id'], $api_setting_id);
            $status = ($prev_status == 'partial') ? $prev_status : $partial_prospect_status;
            // dd($prev_status, $status);
        }
        // dd($status);
        if (count($contact_scrap_to_be_updated) > 0)
            $contact_scrap_to_be_updated['is_updated'] = 1;
        else $contact_scrap_to_be_updated['is_updated'] = 0;
        $contact_scrap_to_be_updated['prospect_verified'] = $status;
        $response['status'] = $status;

        $response['contact_scrap_to_be_updated'] = $contact_scrap_to_be_updated;

        return $response;
    }

    public function filterScrapData($data)
    {
        $final_contact_arr = [];
        foreach ($data as $index => $val) {
            // foreach ($val as $inner_index => $inner_val) {
            if (in_array($val['dataCategoryName'], array('Property', 'Voters'))) {
                $new =  strtotime("-3 years");
                if (strtotime($val['reportedDate']) >= $new) {
                    // if ($contact_data->leads->zip == $val['zip']) {
                    // if (Lead::getStringsSimilarityPercentage($val['address'], $contact_data->leads->address1) > 50) {
                    array_push(
                        $final_contact_arr,
                        $val
                    );
                    // }
                    // }
                }
            }
            // }
        }
        return $final_contact_arr;
    }
}
