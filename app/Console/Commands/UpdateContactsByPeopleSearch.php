<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ScrapApiPlatform;
use App\Traits\CommonFunctionsTrait;
use DateTime;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Command to update contacts by People Search API
 */
class UpdateContactsByPeopleSearch extends Command
{
    use CommonFunctionsTrait;

    // API platform ID
    public $apiPlatformId = 9;

    // Priority tracking
    public $currentApiPriority = 0;
    public $maxApiPriority = 0;

    // Prospect verified status
    public $prospectVerified = ['pending'];

    /** @var string Command signature */
    protected $signature = 'command:update-contacts-by-people-search';

    /** @var string Command description */
    protected $description = 'This is update the contacts by the peoplesearch ';

    /**
     * Execute the console command
     */
    public function handle()
    {
        $this->configureErrorReporting();

        $openSearchApi = $this->fetchApiConfiguration();
        $this->configureApiPriority($openSearchApi);

        $notUpdatedContacts = $this->getNotUpdatedContacts();

        foreach ($notUpdatedContacts as $soloContact) {
            $this->fetchDataFromOpenSearch($soloContact, $openSearchApi, 9);
        }
    }

    /**
     * Configure error reporting settings
     */
    private function configureErrorReporting(): void
    {
        ini_set('display_startup_errors', 1);
        ini_set('display_errors', 1);
        error_reporting(-1);
    }

    /**
     * Fetch API configuration
     */
    private function fetchApiConfiguration(): array
    {
        return ScrapApiPlatform::find($this->apiPlatformId)->toArray();
    }

    /**
     * Configure API priority settings
     */
    private function configureApiPriority(array $openSearchApi): void
    {
        $this->currentApiPriority = (isset($openSearchApi['priority_order']) && $openSearchApi['priority_order'] > 1)
            ? $openSearchApi['priority_order']
            : 0;

        $this->maxApiPriority = ScrapApiPlatform::count('id');

        if (isset($openSearchApi['priority_order']) && $openSearchApi['priority_order'] > 1) {
            $this->prospectVerified = ['unavailable', 'partial'];
        }
    }

    /**
     * Get contacts that need to be updated
     */
    private function getNotUpdatedContacts()
    {
        return Contact::whereIn('prospect_verified', $this->prospectVerified)
            ->where('added_by_scrap_apis', 1)
            ->orderBy('id', 'asc')
            ->limit(1)
            ->get();
    }

    /**
     * Fetch data from Open Search API
     */
    public function fetchDataFromOpenSearch($soloContact, $openSearchApi, $platformId)
    {
        try {
            if ($this->isLeadDeleted($soloContact)) {
                return ['status' => false, 'data' => 'Lead deleted '];
            }

            $validationResult = $this->validateRequiredParams($soloContact, $openSearchApi);
            if ($validationResult !== null) {
                return $validationResult;
            }

            $apiAuthToken = $this->getAuthToken($openSearchApi);
            if (!$apiAuthToken) {
                return ['status' => false, 'data' => 'Auth token Issue '];
            }

            $response = $this->makeApiRequest($soloContact, $openSearchApi, $apiAuthToken);

            return $this->processApiResponse($response, $soloContact, $platformId);
        } catch (\Throwable $err) {
            $this->logError($err);
            throw $err;
        }
    }

    /**
     * Check if lead is deleted
     */
    private function isLeadDeleted($soloContact): bool
    {
        if (empty($soloContact->leads->address1)) {
            DB::table('contacts')->where('id', $soloContact->id)->update([
                'verified_status' => 'Unverified - By Scrap Api',
                'prospect_verified' => 'pending_lead_deleted'
            ]);
            return true;
        }
        return false;
    }

    /**
     * Validate required parameters
     */
    private function validateRequiredParams($soloContact, $openSearchApi)
    {
        $sanitizedAddress = $this->removeAfterKeywords($soloContact->leads->address1);

        $requiredParams = [
            'first_name' => $soloContact->c_first_name,
            'last_name' => $soloContact->c_last_name,
            'address' => $sanitizedAddress,
            'city' => $soloContact->leads->city,
            'state' => $soloContact->leads->state,
            'lead_zip' => $soloContact->leads->zip,
        ];

        foreach ($requiredParams as $key => $value) {
            if (empty($value)) {
                DB::table('contacts')->where('id', $soloContact->id)->update([
                    'verified_status' => 'Unverified - By Scrap Api',
                    'prospect_verified' => 'required_fields_for_open_search_api_missing:' . $key
                ]);
                return ['status' => false, 'data' => 'Missing parameter: ' . $key];
            }
        }

        return null;
    }

    /**
     * Get authentication token
     */
    private function getAuthToken($openSearchApi): string
    {
        if ($openSearchApi['auth_token_required'] != 1) {
            return '';
        }

        $expiryDate = new DateTime($openSearchApi['auth_expiry_date']);
        $now = new DateTime('now');

        if (!empty($openSearchApi['api_auth_token']) && $expiryDate > $now) {
            return $openSearchApi['api_auth_token'];
        }

        $usernameAndKey = [
            'username' => $openSearchApi['api_username'],
            'password' => $openSearchApi['api_key'],
        ];

        return $this->callOpenPeopleAuthentication(
            $openSearchApi['api_auth_url'],
            json_encode($usernameAndKey),
            $this->apiPlatformId
        );
    }

    /**
     * Make API request
     */
    private function makeApiRequest($soloContact, $openSearchApi, $apiAuthToken)
    {
        $sanitizedAddress = $this->removeAfterKeywords($soloContact->leads->address1);
        $url = $openSearchApi['api_contact_search_url'];

        $contact = [
            'firstName' => $soloContact->c_first_name,
            'lastName' => $soloContact->c_last_name,
            'address' => $sanitizedAddress,
            'unit' => null,
            'city' => $soloContact->leads->city,
            'state' => $soloContact->leads->state,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($contact),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiAuthToken,
            ],
        ]);

        $originalResponse = curl_exec($curl);
        curl_close($curl);

        return json_decode($originalResponse, true);
    }

    /**
     * Process API response
     */
    private function processApiResponse($response, $soloContact, $platformId)
    {
        if ($response == null || empty($response)) {
            return false;
        }

        if (isset($response['results']) && count($response['results']) > 0) {
            $this->updateWithOpenSearchData($response['results'], $soloContact, $response, $platformId);
            return ['status' => true, 'data' => $response['results']];
        }

        $status = $this->verifyProspectStatus(
            $this->currentApiPriority,
            $this->maxApiPriority,
            $soloContact->prospect_verified,
            '',
            '',
            '',
            ''
        );

        DB::table('scrap_contact_api_platforms')->insert([
            'contact_id' => $soloContact->id,
            'api_platform_id' => $platformId,
            'api_response' => json_encode($response),
            'status' => $status
        ]);

        DB::table('contacts')->where('id', $soloContact->id)->update([
            'verified_status' => 'Unverified - By Scrap Api',
            'prospect_verified' => $status
        ]);

        return ['status' => true, 'data' => $response['results']];
    }

    /**
     * Call OpenPeople authentication API
     */
    public function callOpenPeopleAuthentication($authUrl, $usernameAndKey, $apiId)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($authUrl, json_decode($usernameAndKey, true));

        if ($response->successful()) {
            ScrapApiPlatform::where('id', $apiId)->update([
                'api_auth_token' => $response->json()['token'],
                'auth_expiry_date' => date($response->json()['token_expiry_utc']),
            ]);

            return $response->json()['token'];
        }

        $this->error('Error: ' . $response->body());
        return null;
    }

    /**
     * Log error details
     */
    private function logError(\Throwable $err): void
    {
        \Log::error('Error in fetchDataFromOpenSearch', [
            'message' => $err->getMessage(),
            'line' => $err->getLine(),
            'file' => $err->getFile(),
            'trace' => $err->getTraceAsString(),
        ]);
    }

    /**
     * Normalize address by replacing abbreviations
     */
    private function normalizeAddress($address)
    {
        $address = strtoupper($address);

        $replacements = [
            'PKWY' => 'PARKWAY',
            'PARKWAY' => 'PARKWAY',
            'APT' => 'APARTMENT',
            'UNIT' => 'UNIT',
            'RD' => 'ROAD',
            'Rd' => 'ROAD',
            'ST' => 'STREET',
            'ST.' => 'STREET',
            'STR' => 'STREET',
            'STR.' => 'STREET',
            'AVE' => 'AVENUE',
            'AVE.' => 'AVENUE',
            'CT' => 'COURT',
            'CT.' => 'COURT',
            'DR' => 'DRIVE',
            'DR.' => 'DRIVE',
            'DRV' => 'DRIVE',
            'BLVD' => 'BOULEVARD',
            'BLVD.' => 'BOULEVARD',
            'HWY' => 'HIGHWAY',
            'HWY.' => 'HIGHWAY',
            'LN' => 'LANE',
            'LN.' => 'LANE',
            'TER' => 'TERRACE',
            'TER.' => 'TERRACE',
            'CIR' => 'CIRCLE',
            'CIR.' => 'CIRCLE',
            'PL' => 'PLACE',
            'PL.' => 'PLACE',
            'TR' => 'TRAIL',
            'TR.' => 'TRAIL',
        ];

        $address = str_replace(array_keys($replacements), array_values($replacements), $address);
        $address = preg_replace('/[^A-Z0-9\s]/', '', $address);

        return trim($address);
    }

    /**
     * Filter Open Search data by criteria
     */
    public function filterOpenScrapData($data, $searchZipcode, $searchAddress)
    {
        $filteredRecords = $this->filterByCategoryAndDate($data, $searchZipcode);

        if (count($filteredRecords) === 1) {
            return $filteredRecords[0];
        }

        return $this->filterByAddressMatch($filteredRecords, $searchAddress);
    }

    /**
     * Filter by category, date, and zipcode
     */
    private function filterByCategoryAndDate($data, $searchZipcode): array
    {
        $finalContactArr = [];
        $threeYearsAgo = strtotime('-3 years');
        $catArray = ['Property', 'Voters', 'Hunt/Fish Licenses'];

        foreach ($data as $val) {
            if ($this->matchesFilterCriteria($val, $catArray, $threeYearsAgo, $searchZipcode)) {
                $finalContactArr[] = $val;
            }
        }

        return $finalContactArr;
    }

    /**
     * Check if record matches filter criteria
     */
    private function matchesFilterCriteria($record, $catArray, $threeYearsAgo, $searchZipcode): bool
    {
        return in_array($record['dataCategoryName'], $catArray)
            && strtotime($record['reportedDate']) >= $threeYearsAgo
            && strpos($record['zip'], $searchZipcode) !== false;
    }

    /**
     * Filter by address match
     */
    private function filterByAddressMatch($records, $searchAddress): array
    {
        if (count($records) <= 1) {
            return $records;
        }

        $addressMatchedRecords = [];

        foreach ($records as $record) {
            if ($this->isAddressMatched($record, $searchAddress)) {
                $addressMatchedRecords[] = $record;
            }
        }

        return $addressMatchedRecords;
    }

    /**
     * Check if address matches with similarity
     */
    private function isAddressMatched($record, $searchAddress): bool
    {
        $sanitizedApiResponse = strtolower($this->removeAfterKeywords($record['address']));
        similar_text($sanitizedApiResponse, strtolower($searchAddress), $percent);

        return $percent >= 80;
    }

    /**
     * Update contact with Open Search data
     */
    public function updateWithOpenSearchData($openSearchData, $soloContact, $response, $platformId)
    {
        $filteredSearchResult = $this->getFilteredSearchResult($openSearchData, $soloContact);
        $contactData = $this->prepareContactData($soloContact, $filteredSearchResult);
        $prospectVerified = $this->getProspectStatus($soloContact, $contactData);

        $this->updateContactRecord($soloContact->id, $contactData, $prospectVerified);
        $this->insertApiPlatformRecord($soloContact->id, $platformId, $response, $prospectVerified);
    }

    /**
     * Get filtered search result
     */
    private function getFilteredSearchResult($openSearchData, $soloContact)
    {
        if (count($openSearchData) === 0) {
            return [];
        }

        return $this->filterOpenScrapData(
            $openSearchData,
            $soloContact->leads->zip,
            $this->removeAfterKeywords($soloContact->leads->address1)
        );
    }

    /**
     * Prepare contact data for update
     */
    private function prepareContactData($soloContact, $filteredSearchResult): array
    {
        if (is_array($filteredSearchResult) && count($filteredSearchResult) > 1) {
            return [
                'phone' => $soloContact->c_phone ?: ($filteredSearchResult['phone'] ?? ''),
                'email' => $soloContact->c_email ?: ($filteredSearchResult['email'] ?? ''),
                'city' => $soloContact->c_city ?: ($filteredSearchResult['city'] ?? ''),
                'zip' => $soloContact->c_zip ?: ($filteredSearchResult['zip'] ?? ''),
                'address1' => $soloContact->c_address1 ?: ($filteredSearchResult['address'] ?? ''),
                'state' => $soloContact->c_state ?: ($filteredSearchResult['state'] ?? ''),
            ];
        }

        return [
            'phone' => '',
            'email' => '',
            'city' => '',
            'zip' => '',
            'address1' => '',
            'state' => '',
        ];
    }

    /**
     * Get prospect verified status
     */
    private function getProspectStatus($soloContact, $contactData): string
    {
        return $this->verifyProspectStatus(
            $this->currentApiPriority,
            $this->maxApiPriority,
            $soloContact->prospect_verified,
            $contactData['address1'],
            $contactData['city'],
            $contactData['zip'],
            $contactData['email']
        );
    }

    /**
     * Update contact record in database
     */
    private function updateContactRecord($contactId, $contactData, $prospectVerified): void
    {
        DB::table('contacts')->updateOrInsert(['id' => $contactId], [
            'verified_status' => 'Unverified - By Scrap Api',
            'prospect_verified' => $prospectVerified,
            'c_zip' => $contactData['zip'],
            'c_city' => $contactData['city'],
            'c_phone' => $contactData['phone'],
            'c_email' => $contactData['email'],
            'c_state' => $contactData['state'],
            'c_address1' => $contactData['address1']
        ]);
    }

    /**
     * Insert API platform record
     */
    private function insertApiPlatformRecord($contactId, $platformId, $response, $prospectVerified): void
    {
        DB::table('scrap_contact_api_platforms')->insert([
            'contact_id' => $contactId,
            'api_platform_id' => $platformId,
            'api_response' => json_encode($response),
            'status' => $prospectVerified
        ]);
    }
}
