<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ScrapApiPlatform;
use Illuminate\Support\Facades\Http;
use DB;
use App\Traits\CommonFunctionsTrait;


class UpdateContactsByDataLabs extends Command
{
	use CommonFunctionsTrait;
	public $apiPlatformId = 1;
	public $currentApiPriority = 0;
	public $maxApiPriority = 0;

	public $prospectVerified = ['pending'];

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:update-contacts-by-datalabs';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This is update the contacts by the datalabs and the ';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}



	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		ini_set('display_startup_errors', 1);
		ini_set('display_errors', 1);
		error_reporting(-1);
		$dataLabsApi = ScrapApiPlatform::find($this->apiPlatformId)->toArray();

		$this->currentApiPriority = (isset($dataLabsApi['priority_order']) && $dataLabsApi['priority_order'] > 1) ? $dataLabsApi['priority_order'] : 0;
		$this->maxApiPriority = ScrapApiPlatform::count('id');


		if (isset($dataLabsApi['priority_order']) && $dataLabsApi['priority_order'] > 1) :
			$this->prospectVerified = ['unavailable', 'partial'];
		endif;


		$notUpdatedContacts = Contact::where('added_by_scrap_apis', 1)
			->whereIn('prospect_verified', $this->prospectVerified)
			// ->where('prospect_verified', 'unavailable') 
			->orderBy('id', 'asc')->limit(30)->get();

		// $notUpdatedContacts = Contact::
		// 	// whereIn('contacts.prospect_verified', $this->prospectVerified)
		// 	where('contacts.added_by_scrap_apis', 1)
		// 	->join('scrap_contact_api_platforms', 'scrap_contact_api_platforms.contact_id', '=', 'contacts.id')
		// 	->whereNotNull('scrap_contact_api_platforms.api_response')
		// 	->where('scrap_contact_api_platforms.api_response', '!=', '')
		// 	->where('scrap_contact_api_platforms.api_platform_id', 9)
		// 	->orderBy('contacts.id', 'desc')
		// 	// ->where('scrap_contact_api_platforms.api_platform_id', 1)
		// 	->limit(1)
		// 	->get();


		foreach ($notUpdatedContacts as $soloContact) :
			echo $soloContact->id;



			$datalabsData = $this->fetchDataFromDataLabs($soloContact, $dataLabsApi, $this->apiPlatformId);

			echo "<pre>";print_r($datalabsData);exit;
		endforeach;
	}

	// Function to filter contacts by postal code
	function filterContactsByPostalCode($data, $postalCode)
	{
		$filteredContacts = [];

		echo $data['location_postal_code'] . '--' . $postalCode;
		if (isset($data['location_postal_code']) && $data['location_postal_code'] == $postalCode) {
			$filteredContacts[] = $data;
		}


		return $filteredContacts;
	}



	public function fetchDataFromDataLabs($soloContact, $dataLabsApi, $platform_id)
	{
		try {
			// Validate the required parameters
			$requiredParams = [
				'first_name' => $soloContact->c_first_name,
				'last_name' => $soloContact->c_last_name,
				"postal_code" => $soloContact->leads->zip,
				'api_key' => $dataLabsApi['api_key']
			];
			print_r($requiredParams);

			foreach ($requiredParams as $key => $value) {
				if (empty($value)) {
					DB::table('contacts')->where('id', $soloContact->id)->update(['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => 'required_fields_for_datatlabs_missing:' . $key]);
					return ['status' => false, 'data' => 'Missing parameter: ' . $key];
				}
			}

			// Construct the URL and query parameters
			$url = $dataLabsApi['api_contact_search_url'];
			$query = [
				'first_name' => $soloContact->c_first_name,
				'last_name' => $soloContact->c_last_name,
				"postal_code" => $soloContact->leads->zip,
				'api_key' => $dataLabsApi['api_key']
			];

			print_r($query);


			// Log the request details
			// \Log::info('Request URL', ['url' => $url, 'query' => $query]);

			// Make the HTTP GET request
			$response = Http::get($url, $query);

			echo "<pre>";print_r($response);

			// Process the response
			if ($response->successful()) {

				$filteredResponse = $this->filterContactsByPostalCode($response->json()['data'], $soloContact->leads->zip);

				echo "<pre>";print_r($filteredResponse);

				$this->updateWithDataLabsData($filteredResponse, $soloContact, $response, $this->apiPlatformId);
				return ['status' => true, 'data' => $filteredResponse];
			} else {

				$status = $this->verifyProspectStatus($this->currentApiPriority, $this->maxApiPriority, $soloContact->prospect_verified,  '', '', '', '');
				DB::table('contacts')->where('id', $soloContact->id)->update(['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => $status]);
				return ['status' => false, 'data' => $response->json()['error']['message']];
				DB::table('scrap_contact_api_platforms')->insert(['contact_id' => $soloContact->id, 'api_platform_id' => $this->apiPlatformId, 'api_response' => $response, 'status' => $status]);
			}
		} catch (\Throwable $err) {
			// Log the error details
			\Log::error('Error in fetchDataFromDataLabs', [
				'message' => $err->getMessage(),
				'line' => $err->getLine(),
				'file' => $err->getFile(),
				'trace' => $err->getTraceAsString()
			]);
			throw ($err);
		}
	}

	public function updateWithDataLabsData($datalabsData, $soloContact, $response, $platform_id)
	{
		$c_email = $c_secondary_email = $c_phone = $c_secondary_phone = $c_address1 = $c_city = $c_state = $c_zip = '';

		if (is_array($datalabsData) && count($datalabsData) == 0) {
			echo 'zip_mismatched';
			// print_r($response);
			$prospect_verified = $this->verifyProspectStatus($this->currentApiPriority, $this->maxApiPriority, $soloContact->prospect_verified, $c_address1, $c_city, $c_zip, $c_email);

			DB::table('contacts')->updateOrInsert(['id' => $soloContact->id], ['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => $prospect_verified, 'c_zip' => $c_zip, 'c_city' => $c_city, 'c_secondary_phone' => $c_secondary_phone, 'c_phone' => $c_phone, 'c_email' => $c_email, 'c_secondary_email' => $c_secondary_email, 'c_address1' => $c_address1]);

			DB::table('scrap_contact_api_platforms')->insert(['contact_id' => $soloContact->id, 'api_platform_id' => $this->apiPlatformId, 'api_response' => $response, 'status' => $prospect_verified]);
			return;
		}

		$c_email = $c_secondary_email = $c_phone = $c_secondary_phone = $c_address1 = $c_city = $c_state = $c_zip = '';
		$recommended_personal_email = (isset($datalabsData['0']['recommended_personal_email']) && !empty($datalabsData['0']['recommended_personal_email'])) ? $datalabsData['0']['recommended_personal_email'] : '';

		$contactEmails = (isset($datalabsData['0']['personal_emails']) && is_array($datalabsData['0']['personal_emails']) && count($datalabsData['0']['personal_emails'])) > 0 ? $this->prioritizeEmails($datalabsData['0']['personal_emails'], $recommended_personal_email) : [];
		if (count($contactEmails) > 0) {

			$c_email = isset($contactEmails[0]) && !empty($contactEmails[0]) ? $contactEmails[0] : $soloContact->c_email;
			$c_secondary_email = isset($contactEmails[1]) && !empty($contactEmails[1]) ? $contactEmails[1] : $soloContact->c_secondary_email;
		}


		$recommended_c_phone = (isset($datalabsData['0']['mobile_phone']) && !empty($datalabsData['0']['mobile_phone'])) ? $datalabsData['0']['mobile_phone'] : '';

		$contactPhones = (isset($datalabsData['0']['phone_numbers']) && is_array($datalabsData['0']['phone_numbers']) && count($datalabsData['0']['phone_numbers'])) > 0 ? $this->prioritizePhones($recommended_c_phone, $datalabsData['0']['phone_numbers']) : [];

		if (count($contactPhones) > 0) {

			$c_phone = isset($contactPhones[0]) && !empty($contactPhones[0]) ? $contactPhones[0] : $soloContact->c_phone;
			$c_secondary_phone = isset($contactPhones[1]) && !empty($contactPhones[1]) ? $contactPhones[1] : $soloContact->c_secondary_phone;
		}




		$c_address1 = (isset($datalabsData['0']['location_street_address']) && !empty($datalabsData['0']['location_street_address'])) ? $datalabsData['0']['location_street_address'] : $soloContact->c_address1;
		$c_city = (isset($datalabsData['0']['location_locality']) && !empty($datalabsData['0']['location_locality'])) ? $datalabsData['0']['location_locality'] : $soloContact->c_city;
		$c_zip = (isset($datalabsData['0']['location_postal_code']) && !empty($datalabsData['0']['location_postal_code'])) ? $datalabsData['0']['location_postal_code'] : $soloContact->c_zip;
		$c_state = (isset($datalabsData['0']['location_region']) && !empty($datalabsData['0']['location_region']) && ($datalabsData['0']['location_region'] == 'florida' || $datalabsData['0']['location_region'] == 'Florida')) ? $datalabsData['0']['location_region'] : $soloContact->c_state;







		// update contacts table
		echo $prospect_verified = $this->verifyProspectStatus($this->currentApiPriority, $this->maxApiPriority, $soloContact->prospect_verified, $c_address1, $c_city, $c_zip, $c_email);


		DB::table('contacts')->updateOrInsert(['id' => $soloContact->id], ['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => $prospect_verified, 'c_zip' => $c_zip, 'c_city' => $c_city, 'c_secondary_phone' => $c_secondary_phone, 'c_phone' => $c_phone, 'c_email' => $c_email, 'c_secondary_email' => $c_secondary_email, 'c_address1' => $c_address1, 'c_state' => $c_state]);

		DB::table('scrap_contact_api_platforms')->insert(['contact_id' => $soloContact->id, 'api_platform_id' => $this->apiPlatformId, 'api_response' => $response, 'status' => $prospect_verified]);

		//update 

		$otherInfos = [];

		$otherInfos['linkedin_url'] = $datalabsData['0']['linkedin_url'] ?? null;
		$otherInfos['linkedin_username'] = $datalabsData['0']['linkedin_username']  ?? null;
		$otherInfos['linkedin_id'] = $datalabsData['0']['linkedin_id'] ?? null;
		$otherInfos['facebook_url'] = $datalabsData['0']['facebook_url'] ?? null;
		$otherInfos['facebook_username'] = $datalabsData['0']['facebook_username']  ?? null;
		$otherInfos['facebook_id'] = $datalabsData['0']['facebook_id']  ?? null;
		$otherInfos['twitter_url'] = $datalabsData['0']['twitter_url']  ?? null;
		$otherInfos['twitter_username'] = $datalabsData['0']['twitter_username'] ?? null;
		$otherInfos['github_url'] = $datalabsData['0']['github_url'] ?? null;
		$otherInfos['github_username'] = $datalabsData['0']['github_username']  ?? null;

		$notNUllData = array_filter($otherInfos, function ($value) {
			// echo $value;
			return $value !== null && $value !== '';
		});



		print_r($otherInfos);
		print_r($notNUllData);
		if (count($notNUllData) > 0) :

			echo 'hello';
			print_r($otherInfos);
			echo $soloContact->id;
			DB::table('scrap_contact_social_profile')->updateOrInsert(['contact_id' => $soloContact->id], $otherInfos);
		endif;
	}

	function prioritizeEmails($emails, $recommended_personal_email)
	{
		$acceptedProviders = ['gmail', 'yahoo', 'outlook', 'hotmail', 'aol', 'worldnet.att.net'];
		$prioritizedEmails = [];

		foreach ($acceptedProviders as $provider) {
			foreach ($emails as $email) {
				$domain = substr(strrchr($email, "@"), 1); // Get the domain part of the email
				if ($domain && stripos($domain, $provider) !== false) {
					$prioritizedEmails[] = $email;
				}
			}
		}
		$result = [];
		if (!empty($recommended_personal_email)) {
			$second_email = $prioritizedEmails[0] ?? '';
			return $recommended_personal_email === $second_email ? [$recommended_personal_email, ''] : [$recommended_personal_email, $second_email];
		} else {
			$first_email = $prioritizedEmails[0] ?? '';
			$second_email = $prioritizedEmails[1] ?? '';
			return $first_email === $second_email ? [$first_email, ''] : [$first_email, $second_email];
		}

		return $result;
	}
	// Function to remove +1 prefix
	function removePlusOne($number)
	{
		return preg_replace('/^\+1/', '', $number);
	}

	function prioritizePhones($mobile, $otherPhoneNumbers)
	{


		// Remove +1 from $mobile if present
		if ($mobile) {
			$mobile = $this->removePlusOne($mobile);
		}

		// Remove +1 from $otherPhoneNumbers if present
		foreach ($otherPhoneNumbers as &$number) {
			$number = $this->removePlusOne($number);
		}

		// Determine primary and secondary numbers
		$primary = $mobile ? $mobile : (isset($otherPhoneNumbers[0]) ? $otherPhoneNumbers[0] : null);
		$secondary = null;

		if ($mobile) {
			$secondary = isset($otherPhoneNumbers[0]) ? $otherPhoneNumbers[0] : null;
		} else {
			$secondary = isset($otherPhoneNumbers[1]) ? $otherPhoneNumbers[1] : (isset($otherPhoneNumbers[0]) ? $otherPhoneNumbers[0] : null);
		}

		// Check if primary and secondary are the same
		if ($primary === $secondary) {
			$secondary = '';
		}

		return [
			$primary,
			$secondary,
		];
	}
}
