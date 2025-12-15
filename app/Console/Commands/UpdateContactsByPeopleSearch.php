<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ScrapApiPlatform;
use Illuminate\Support\Facades\Http;
use DB;
use DateTime;
use App\Traits\CommonFunctionsTrait;


class UpdateContactsByPeopleSearch extends Command
{
	use CommonFunctionsTrait;

	public $apiPlatformId = 9;
	public $currentApiPriority = 0;
	public $maxApiPriority = 0;

	public $prospectVerified = ['pending'];

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:update-contacts-by-people-search';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This is update the contacts by the peoplesearch ';

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

		$openSearchApi = ScrapApiPlatform::find($this->apiPlatformId)->toArray();
		$this->currentApiPriority = (isset($openSearchApi['priority_order']) && $openSearchApi['priority_order'] > 1) ? $openSearchApi['priority_order'] : 0;
		$this->maxApiPriority = ScrapApiPlatform::count('id');


		if (isset($openSearchApi['priority_order']) && $openSearchApi['priority_order'] > 1) :
			$this->prospectVerified = ['unavailable', 'partial'];
		endif;


		$notUpdatedContacts = Contact::whereIn('prospect_verified', $this->prospectVerified)->where('added_by_scrap_apis', 1)
			// ->where(function ($query) {
			// 	$query->whereNull('c_phone') 
			// 		->orWhere('c_phone', '')
			// 		->whereNull('c_address1')
			// 		->orWhere('c_address1', '')
			// 		->whereNull('c_zip')
			// 		->orWhere('c_zip', '')
			// 		->whereNull('c_city')
			// 		->orWhere('c_city', '')
			// 		->whereNull('c_state')
			// 		->orWhere('c_state', '');
			// })
			// ->orderBy('id', 'asc')->limit(30)->get();
			->orderBy('id', 'asc')->limit(1)->get();

		foreach ($notUpdatedContacts as $soloContact) :
			echo $soloContact->id;
			echo '---';
			$datalabsData = $this->fetchDataFromOpenSearch($soloContact, $openSearchApi, 9);
			echo "<pre>";print_r($datalabsData);exit;
		endforeach;
	}


	public function callOpenPeopleAuthentication($auth_url, $username_and_key, $api_id)
	{

		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
		])->post($auth_url, json_decode($username_and_key, true));

		if ($response->successful()) {
			$Lead = ScrapApiPlatform::where('id', $api_id)
				->update([
					'api_auth_token' => $response->json()['token'],
					'auth_expiry_date' =>  Date($response->json()['token_expiry_utc']),
				]);
			return $response->json()['token'];
		} else {
			$this->error('Error: ' . $response->body());
			return null;
		}
	}


	public function fetchDataFromOpenSearch($soloContact, $openSearchApi, $platform_id)
	{
		try {

			if(empty($soloContact->leads->address1)){
				DB::table('contacts')->where('id', $soloContact->id)->update(['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => 'pending_lead_deleted']);

				return ['status' => false, 'data' => 'Lead deleted ']; 
			}

			$sanitizedAddress = $this->removeAfterKeywords($soloContact->leads->address1);

			// Validate the required parameters
			$requiredParams = [
				'first_name' => $soloContact->c_first_name,
				'last_name' => $soloContact->c_last_name,
				'address' => $sanitizedAddress,
				'city' => $soloContact->leads->city,
				'state' => $soloContact->leads->state,
				'lead_zip' => $soloContact->leads->zip,
			];
			print_r($requiredParams);
			print_r($openSearchApi);


			foreach ($requiredParams as $key => $value) {
				if (empty($value)) {
					DB::table('contacts')->where('id', $soloContact->id)->update(['verified_status'=> 'Unverified - By Scrap Api', 'prospect_verified' => 'required_fields_for_open_search_api_missing:' . $key]);
					return ['status' => false, 'data' => 'Missing parameter: ' . $key];
				}
			}

			$apiAuthToken = '';
			// Check for the authentication token start
			if ($openSearchApi['auth_token_required'] == 1) {
			    $expiryDate = new DateTime($openSearchApi['auth_expiry_date']); 
			    $now = new DateTime('now');

			    if (!empty($openSearchApi['api_auth_token']) && $expiryDate > $now) {

			        $apiAuthToken = $openSearchApi['api_auth_token'];
			        echo "token exist";

			    } else {

			        echo "token not found";

			        $username_and_key = [
			            'username' => $openSearchApi['api_username'],
			            'password' => $openSearchApi['api_key'],
			        ];

			        $apiAuthToken = $this->callOpenPeopleAuthentication(
			            $openSearchApi['api_auth_url'],
			            json_encode($username_and_key),
			            $this->apiPlatformId
			        );
			    }
			}

			// Check for the authentication token end



			if (!$apiAuthToken) {
				return ['status' => false, 'data' => 'Auth token Issue '];
			}

			// Construct the URL and query parameters
			$url = $openSearchApi['api_contact_search_url'];



			$contact['firstName'] = $soloContact->c_first_name;
			$contact['lastName'] =  $soloContact->c_last_name;
			$contact['address'] = $sanitizedAddress;
			$contact['unit'] = null;
			$contact['city'] = $soloContact->leads->city;
			$contact['state'] =  $soloContact->leads->state;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
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
					'Authorization: Bearer ' . $apiAuthToken
				),
			));

			$original_response = $response = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($response, true);
			echo "<pre>"; echo "response  -----"; echo "<br>";print_r($response);

			if ($response != null || !empty($response)) {

				if (isset($response['results']) && count($response['results']) > 0) {

					$this->updateWithOpenSearchData($response['results'], $soloContact, $original_response, $platform_id);
					// die();
				} else {
					$status = $this->verifyProspectStatus($this->currentApiPriority, $this->maxApiPriority, $soloContact->prospect_verified,  '', '', '', '');
					DB::table('scrap_contact_api_platforms')->insert(['contact_id' => $soloContact->id, 'api_platform_id' => $platform_id, 'api_response' => $original_response, 'status' => $status]);
					DB::table('contacts')->where('id', $soloContact->id)->update(['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => $status]);
				}

				return ['status' => true, 'data' => $response['results']];
			} else {
				return false;
			}
		} catch (\Throwable $err) {
			// Log the error details
			\Log::error('Error in fetchDataFromOpenSearch', [
				'message' => $err->getMessage(),
				'line' => $err->getLine(),
				'file' => $err->getFile(),
				'trace' => $err->getTraceAsString()
			]);
			throw ($err);
		}
	}

	function normalizeAddress($address)
	{
		// Convert to uppercase
		$address = strtoupper($address);
		// Replace common abbreviations with full words
		$replacements = [
			'PKWY' => 'PARKWAY', 'PARKWAY' => 'PARKWAY', 'APT' => 'APARTMENT', 'UNIT' => 'UNIT',
			'RD' => 'ROAD', 'Rd' => 'ROAD', 'ST' => 'STREET', 'ST.' => 'STREET', 'STR' => 'STREET', 'STR.' => 'STREET',
			'AVE' => 'AVENUE', 'AVE.' => 'AVENUE', 'CT' => 'COURT', 'CT.' => 'COURT',
			'DR' => 'DRIVE', 'DR.' => 'DRIVE', 'DRV' => 'DRIVE', 'BLVD' => 'BOULEVARD', 'BLVD.' => 'BOULEVARD',
			'HWY' => 'HIGHWAY', 'HWY.' => 'HIGHWAY', 'LN' => 'LANE', 'LN.' => 'LANE',
			'TER' => 'TERRACE', 'TER.' => 'TERRACE', 'CIR' => 'CIRCLE', 'CIR.' => 'CIRCLE',
			'PL' => 'PLACE', 'PL.' => 'PLACE', 'TR' => 'TRAIL', 'TR.' => 'TRAIL'
		];
		$address = str_replace(array_keys($replacements), array_values($replacements), $address);
		// Remove extraneous characters
		$address = preg_replace('/[^A-Z0-9\s]/', '', $address);
		return trim($address);
	}

	public function filterOpenScrapData($data, $searchZipcode, $searchAddress)
	{
		// echo $searchZipcode . '---' . $searchAddress;
		$final_contact_arr = [];
		$new = strtotime("-3 years");

		// Step 1: Filter records by 'Property', 'Voters', 'Hunt/Fish Licenses' and reported within the last 3 years
		$zipcounter = 0;

		foreach ($data as $val) {
			if (in_array($val['dataCategoryName'], ['Property', 'Voters', 'Hunt/Fish Licenses']) && strtotime($val['reportedDate']) >= $new) {
				if (strpos($val['zip'], $searchZipcode) !== false) {
					$final_contact_arr[] = $val;
					$zipcounter++;
				}
			}
		}

		// Step 2: If there is only one record, return it
		if (count($final_contact_arr) === 1) {
			return $final_contact_arr[0];
		}

		// Function to normalize addresses


		// Step 3: Normalize the search address
		$normalizedSearchAddress = $this->normalizeAddress($searchAddress);


		// Step 4: If there are multiple records, filter by matching address
		$address_matched_records = [];
		$i = 0;
		foreach ($final_contact_arr as $record) {
			$sanitizedapi_response = strtolower($this->removeAfterKeywords($record['address']));
			// echo 'api address=> ' . $record['address'] . '---- comparer address =>' . strtolower($searchAddress) . '%% sanitized ' . $sanitizedapi_response . '+++';
			similar_text($sanitizedapi_response, strtolower($searchAddress), $percent);
			// echo $percent . '=====&&&&';
			if ($percent >= 80) { // 80% similarity
				$address_matched_records[] = $record;
				$i++;
			}
		}



		// Step 5: If there is only one or more record after address match, return it
		if (count($address_matched_records) >= 1) {
			return $address_matched_records[0];
		}

		return [];
	}



	public function updateWithOpenSearchData($OpenSearchData, $soloContact, $response, $platform_id)
	{
		$c_email  = $c_phone  = $c_address1 = $c_city = $c_state = $c_zip = '';
		$FilteredSearchResult =  count($OpenSearchData) > 0 ? $this->filterOpenScrapData($OpenSearchData, $soloContact->leads->zip, $this->removeAfterKeywords($soloContact->leads->address1)) : [];

		if (is_array($FilteredSearchResult) &&  count($FilteredSearchResult) > 1) {
			print_r($FilteredSearchResult);

			echo	$c_phone = ($soloContact->c_phone) ? ($soloContact->c_phone) : $FilteredSearchResult['phone'];
			echo '---' .	$c_email = ($soloContact->c_email) ? ($soloContact->c_email) : $FilteredSearchResult['email'];
			echo '---' .	$c_city = ($soloContact->c_city) ? ($soloContact->c_city) : $FilteredSearchResult['city'];
			echo '---' .	$c_zip = ($soloContact->c_zip) ? ($soloContact->c_zip) : $FilteredSearchResult['zip'];
			echo '---' .	$c_address1 = ($soloContact->c_address1) ? ($soloContact->c_address1) : $FilteredSearchResult['address'];
			echo '---' .	$c_state = ($soloContact->c_state) ? ($soloContact->c_state) : $FilteredSearchResult['state'];
		}

		$prospect_verified = $this->verifyProspectStatus($this->currentApiPriority, $this->maxApiPriority, $soloContact->prospect_verified, $c_address1, $c_city, $c_zip, $c_email);

		// update contacts table
		DB::table('contacts')->updateOrInsert(['id' => $soloContact->id], ['verified_status'=> 'Unverified - By Scrap Api','prospect_verified' => $prospect_verified, 'c_zip' => $c_zip, 'c_city' => $c_city,  'c_phone' => $c_phone, 'c_email' => $c_email, 'c_state' => $c_state, 'c_address1' => $c_address1]);

		DB::table('scrap_contact_api_platforms')->insert(['contact_id' => $soloContact->id, 'api_platform_id' => $platform_id, 'api_response' => $response, 'status' => $prospect_verified]);
	}
}
