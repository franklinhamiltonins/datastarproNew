<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Model;
use DB;
use Error;
use Illuminate\Support\Arr;
use PhpParser\Node\Expr\Throw_;

class ScrapDataLabs extends Model
{

	public static function callForDataLabsApi($contacts, $api_setting_data)
	{
		try {
			$contacts_updated_arr = [];
			$contacts_skipped_arr = [];
			$store_contact_api_platform = [];
			$store_social_profile_data = [];
			$contacts_updated_count = 0;
			$contacts_skipped_count = 0;
			$skipped_prospect_status = 'not found';
			$partial_prospect_status = 'not found';
			$prospect_merged_status = 'processed_next';
			$scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($api_setting_data->id);

			if (count($scrap_platform_settings) > 0) {
				$skipped_prospect_status = 'unavailable';
				$prospect_merged_status = 'pending';
			}
			if (count($scrap_platform_settings) > 0)
				$partial_prospect_status = 'unavailable';

			if ($api_setting_data['auth_token_required'] == 0) {
				foreach ($contacts as $key => $val) {
					$call_open_people_search_arr =  self::callPeopleDataLabsSearch($val, $api_setting_data);
					// dd($call_open_people_search_arr);
					if ($call_open_people_search_arr['status']) {

						$contact_scrap_to_be_updated
							= self::formatContactScrapUpdate($call_open_people_search_arr['data'], $partial_prospect_status, $val, $api_setting_data['id']);

						Contact::updateContactsWithScrapData($contact_scrap_to_be_updated['contact_scrap_to_be_updated'], $val['id']);

						if (count($contact_scrap_to_be_updated['social_profile_data']) > 0) {
							$store_social_profile_data[] = array_merge($contact_scrap_to_be_updated['social_profile_data'], array('contact_id' => $val['id']), array('created_at' => date('y-m-d H:i:s')));
						}

						$store_contact_api_platform[] = array('contact_id' => $val['id'], 'status' => $contact_scrap_to_be_updated['status'], 'api_platform_id' => $api_setting_data['id'], 'record_name' => $api_setting_data['platform_name'] . '_' . date('y-m-d'), 'merged_status' => $prospect_merged_status, 'created_at' => date('y-m-d H:i:s'));

						$contacts_updated_count++;
					} else {
						$prev_status = Contact::checkForCurrentStatus($val['id'], $api_setting_data['id']);


						$status = ($prev_status == 'partial') ? $prev_status : $skipped_prospect_status;
						// dd($prev_status, $status);
						Contact::updateContactsWithScrapData(['prospect_verified' => $status], $val['id']);

						$store_contact_api_platform[] = array('contact_id' => $val['id'], 'status' => $status, 'api_platform_id' => $api_setting_data['id'], 'record_name' => $api_setting_data['platform_name'] . '_' . date('y-m-d'), 'merged_status' => $prospect_merged_status, 'created_at' => date('y-m-d H:i:s'));
						$contacts_skipped_count++;
					}
					$contacts_updated_arr['contacts_used'][] = $val['c_first_name'];
				}

				//call store Contact Api Platform Status
				Contact::storeContactApiPlatformStatus($store_contact_api_platform);
				//call store Social Profile Data
				if (count($contact_scrap_to_be_updated['social_profile_data']) > 0) {
					// $store_social_profile_data['social_profile_data']['created_at'] = date('y-m-d H:i:s');
					Contact::storeSocialProfileData($store_social_profile_data);
				}
				$contacts_updated_arr['contacts_updated_count'] = $contacts_updated_count;
				$contacts_updated_arr['contacts_skipped_count'] = $contacts_skipped_count;
				$contacts_updated_arr['contacts_used'] = $val['c_first_name'];
				return ['status' => 'true', 'data' => $contacts_updated_arr, 'message' => 'Records updated.'];
			}
			throw new Error('Something unexpected happened!');
		} catch (\Throwable $err) {
			Log::channel('callForDataLabsApi')->info('Scrap status of ' . $err);
			toastr()->error($err);
			throw ($err);
		}
	}


	//People DataLabs search curl
	public function callPeopleDataLabsSearch($contact_data, $api_setting_data)
	{
		try {
			$curl_url = $api_setting_data['api_contact_search_url'];
			$curl_url .= '?first_name=' . $contact_data['c_first_name'] . '&last_name=' . $contact_data['c_last_name'] . '&region=' . $contact_data['leads']['state'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $curl_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'X-Api-Key: ' . $api_setting_data['api_key'],
			]);

			$response = curl_exec($ch);

			curl_close($ch);
			$response = json_decode($response, true);
			// return $response;
			if ($response['status'] == 200)
				return ['status' => true, 'data' => $response['data']];
			else
				return ['status' => false, 'data' => $response['error']['message']];
		} catch (\Throwable $err) {
			toastr()->error($err);
			throw ($err);
		}
	}

	// public function formatContactScrapUpdate($data, $partial_prospect_status)
	// {
	//     $response = [];
	//     $status = $partial_prospect_status;
	//     $flag = false;
	//     $contact_scrap_to_be_updated = array();
	//     if ($data['mobile_phone'] != null) {
	//         $status = 'success';
	//         $flag = true;
	//         $contact_scrap_to_be_updated['c_phone'] = $data['mobile_phone'];
	//     } else
	//         $status = $partial_prospect_status;
	//     if (count($data['phone_numbers']) > 0)
	//         $contact_scrap_to_be_updated['c_secondary_phone'] = $data['phone_numbers'][0];

	//     if ($data['recommended_personal_email'] != null) {
	//         // $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
	//         $contact_scrap_to_be_updated['c_email'] = $data['recommended_personal_email'];
	//     } else
	//         $status = $partial_prospect_status;
	//     if ($data['location_street_address'] != null) {
	//         $flag = true;
	//         $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
	//         $contact_scrap_to_be_updated['c_address1'] = $data['location_street_address'];
	//     } else
	//         $status = $partial_prospect_status;
	//     if ($data['location_address_line_2'] != null)
	//         $contact_scrap_to_be_updated['c_address2'] = $data['location_address_line_2'];
	//     else
	//         $status = $partial_prospect_status;
	//     if ($data['location_locality'] != null) {
	//         // $flag = true;
	//         $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
	//         $contact_scrap_to_be_updated['c_city'] = $data['location_locality'];
	//     } else
	//         $status = $partial_prospect_status;
	//     if ($data['location_postal_code'] != null) {
	//         // $flag = true;
	//         $status = ($status == $partial_prospect_status) ? $partial_prospect_status : 'success';
	//         $contact_scrap_to_be_updated['c_zip'] = $data['location_postal_code'];
	//     } else
	//         $status = $partial_prospect_status;
	//     $contact_scrap_to_be_updated['c_secondary_email'] = self::formatEmailFields($data);
	//     // if($flag == true)
	//     $get_social_profile_data = self::getSocialProfileData($data);
	//     $contact_scrap_to_be_updated['prospect_verified'] = $status;
	//     // array_push($response, $contact_scrap_to_be_updated, $get_social_profile_data);
	//     $response['status'] = $status;
	//     $response['contact_scrap_to_be_updated'] = $contact_scrap_to_be_updated;
	//     $response['social_profile_data'] = $get_social_profile_data;
	//     return $response;
	// }

	public function formatContactScrapUpdate($data, $partial_prospect_status, $contact_data, $api_setting_id)
	{
		$response = [];
		$status = $partial_prospect_status;
		$contact_scrap_to_be_updated = array();

		if (($data['mobile_phone'] != null || $data['mobile_phone'] != '') && $data['location_street_address'] != null && $data['location_locality'] != null && $data['location_postal_code'] != null) {
			$status = 'success';

			if (!isset($contact_data['c_phone']) ||  $contact_data['c_phone'] == '')
				$contact_scrap_to_be_updated['c_phone'] = $data['mobile_phone'];
			if (!isset($contact_data['c_secondary_phone']) ||  $contact_data['c_secondary_phone'] == '')
				$contact_scrap_to_be_updated['c_secondary_phone'] = count($data['phone_numbers']) > 0 ? $data['phone_numbers'][0] : '';
			if ($contact_data['c_address1'] == '')
				$contact_scrap_to_be_updated['c_address1'] =  $data['location_street_address'];;
			if ($contact_data['c_city'] == '')
				$contact_scrap_to_be_updated['c_city'] = $data['location_locality'];;
			if ($contact_data['c_zip'] == '')
				$contact_scrap_to_be_updated['c_zip'] = $data['location_postal_code'];
		} elseif ($data['mobile_phone'] != null  || $data['location_street_address'] != null || $data['location_locality'] != null || $data['location_postal_code'] != null) {
			$status = 'partial';
			if (!isset($contact_data['c_phone']) ||  $contact_data['c_phone'] == '')
				$contact_scrap_to_be_updated['c_phone'] = $data['mobile_phone'];
			if (!isset($contact_data['c_secondary_phone']) ||  $contact_data['c_secondary_phone'] == '')
				$contact_scrap_to_be_updated['c_secondary_phone'] = count($data['phone_numbers']) > 0 ? $data['phone_numbers'][0] : '';
			if ($contact_data['c_address1'] == '')
				$contact_scrap_to_be_updated['c_address1'] =  $data['location_street_address'];;
			if ($contact_data['c_city'] == '')
				$contact_scrap_to_be_updated['c_city'] = $data['location_locality'];;
			if ($contact_data['c_zip'] == '')
				$contact_scrap_to_be_updated['c_zip'] = $data['location_postal_code'];
		} elseif ($data['mobile_phone'] == null  && $data['location_street_address'] == null && $data['location_locality'] == null && $data['location_postal_code'] == null) {
			$prev_status = Contact::checkForCurrentStatus($contact_data['id'], $api_setting_id);

			$status = ($prev_status == 'partial') ? $prev_status : $partial_prospect_status;
		}
		if (count($contact_scrap_to_be_updated) > 0)
			$contact_scrap_to_be_updated['is_updated'] = 1;
		else $contact_scrap_to_be_updated['is_updated'] = 0;

		if ($contact_data['c_email'] != '') {
			$contact_scrap_to_be_updated['c_email'] = $data['recommended_personal_email'] != null ? $data['recommended_personal_email'] : '';
		}
		if ($contact_data['c_address2'] != '')
			$contact_scrap_to_be_updated['c_address2'] = $data['location_address_line_2'] ? $data['location_address_line_2'] : '';


		$contact_scrap_to_be_updated['c_secondary_email'] = self::formatEmailFields($data);

		$get_social_profile_data = self::getSocialProfileData($data);

		$contact_scrap_to_be_updated['prospect_verified'] = $status;
		$response['status'] = $status;
		$response['contact_scrap_to_be_updated'] = $contact_scrap_to_be_updated;
		$response['social_profile_data'] = $get_social_profile_data;
		return $response;
	}


	public function formatEmailFields($data)
	{
		$accepted_mail_providers = [
			'gmail', 'yahoo', 'outlook', 'hotmail', 'aol', 'worldnet.att.net'
		];
		$secondary_email = '';
		$email_arr_others = [];
		if (count($data['emails']) > 0) {
			foreach ($accepted_mail_providers as $key => $val) {
				array_push($email_arr_others, $val);
			}
		}
		$email_arr = count($data['personal_emails']) > 0 ? $data['personal_emails'] : (count($email_arr_others) > 0 ? $email_arr_others : []);
		if (count($email_arr) > 0) {
			foreach ($accepted_mail_providers as $key => $val) {
				if (in_array($val, $email_arr)) {
					return $val;
					break;
				}
			}
		}
		return '';
	}

	public function fetchExampleDatalabResponse()
	{
		return [
			"status" => true,
			"data" => [
				"id" => "iAyqxZRcbeU3BPgBtCpVRg_0000",
				"full_name" => "craigminegar",
				"first_name" => "craig",
				"middle_initial" => "a",
				"middle_name" => null,
				"last_initial" => "m",
				"last_name" => "minegar",
				"sex" => null,
				"birth_year" => 1955,
				"birth_date" => "1955",
				"linkedin_url" => null,
				"linkedin_username" => null,
				"linkedin_id" => null,
				"facebook_url" => null,
				"facebook_username" => null,
				"facebook_id" => null,
				"twitter_url" => null,
				"twitter_username" => null,
				"github_url" => null,
				"github_username" => null,
				"work_email" => null,
				"recommended_personal_email" => "zolasandra@gmail.com",
				"mobile_phone" => null,
				"industry" => "hospital&healthcare",
				"location_name" => "winterpark,florida,unitedstates",
				"location_locality" => "winterpark",
				"location_metro" => "orlando,florida",
				"location_region" => "florida",
				"location_country" => "unitedstates",
				"location_continent" => "northamerica",
				"location_street_address" => "1500mizellavenue",
				"location_address_line_2" => null,
				"location_postal_code" => "32789",
				"location_geo" => "28.59,-81.33",
				"location_last_updated" => null,
				"education" => [],
				"profiles" => [],
			]
		];
	}

	public function getSocialProfileData($data)
	{
		$contact_scrap_to_be_updated = [];
		if (($data['linkedin_url'] != null))
			$contact_scrap_to_be_updated['linkedin_url'] = $data['linkedin_url'];
		if ($data['linkedin_username'] != null)
			$contact_scrap_to_be_updated['linkedin_username'] = $data['linkedin_username'];
		if ($data['linkedin_id'] != null)
			$contact_scrap_to_be_updated['linkedin_id'] = $data['linkedin_id'];
		if ($data['facebook_url'] != null)
			$contact_scrap_to_be_updated['facebook_url'] = $data['facebook_url'];
		if ($data['facebook_username'] != null)
			$contact_scrap_to_be_updated['facebook_username'] = $data['facebook_username'];
		if ($data['facebook_id'] != null)
			$contact_scrap_to_be_updated['facebook_id'] = $data['facebook_id'];
		if (($data['twitter_url'] != null))
			$contact_scrap_to_be_updated['twitter_url'] = $data['twitter_url'];
		if ($data['twitter_username'] != null)
			$contact_scrap_to_be_updated['twitter_username'] = $data['twitter_username'];
		if ($data['github_url'] != null)
			$contact_scrap_to_be_updated['github_url'] = $data['github_url'];
		if ($data['github_username'] != null)
			$contact_scrap_to_be_updated['github_username'] = $data['github_username'];

		return $contact_scrap_to_be_updated;
	}
}
