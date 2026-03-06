<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ScrapApiPlatform;
use App\Traits\CommonFunctionsTrait;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Command to update contacts by DataLabs API
 */
class UpdateContactsByDataLabs extends Command
{
    use CommonFunctionsTrait;

    // API platform ID
    public $apiPlatformId = 1;

    // Priority tracking
    public $currentApiPriority = 0;
    public $maxApiPriority = 0;

    // Prospect verified status
    public $prospectVerified = ['pending'];

    /** @var string Command signature */
    protected $signature = 'command:update-contacts-by-datalabs';

    /** @var string Command description */
    protected $description = 'This is update the contacts by the datalabs';

    /**
     * Execute the console command
     */
    public function handle()
    {
        $this->configureErrorReporting();

        $dataLabsApi = $this->fetchApiConfiguration();
        $this->configureApiPriority($dataLabsApi);

        $notUpdatedContacts = $this->getNotUpdatedContacts();

        foreach ($notUpdatedContacts as $soloContact) {
            $this->fetchDataFromDataLabs($soloContact, $dataLabsApi, $this->apiPlatformId);
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
    private function configureApiPriority(array $dataLabsApi): void
    {
        $this->currentApiPriority = (isset($dataLabsApi['priority_order']) && $dataLabsApi['priority_order'] > 1)
            ? $dataLabsApi['priority_order']
            : 0;

        $this->maxApiPriority = ScrapApiPlatform::count('id');

        if (isset($dataLabsApi['priority_order']) && $dataLabsApi['priority_order'] > 1) {
            $this->prospectVerified = ['unavailable', 'partial'];
        }
    }

    /**
     * Get contacts that need to be updated
     */
    private function getNotUpdatedContacts()
    {
        return Contact::where('added_by_scrap_apis', 1)
            ->whereIn('prospect_verified', $this->prospectVerified)
            ->orderBy('id', 'asc')
            ->limit(30)
            ->get();
    }

    /**
     * Fetch data from DataLabs API
     */
    public function fetchDataFromDataLabs($soloContact, $dataLabsApi, $platformId)
    {
        try {
            $validationResult = $this->validateRequiredParams($soloContact, $dataLabsApi);
            if ($validationResult !== null) {
                return $validationResult;
            }

            $response = $this->makeApiRequest($soloContact, $dataLabsApi);

            if ($response->successful()) {
                return $this->handleSuccessfulResponse($response, $soloContact, $platformId);
            }

            return $this->handleFailedResponse($response, $soloContact, $platformId);
        } catch (\Throwable $err) {
            $this->logError($err);
            throw $err;
        }
    }

    /**
     * Validate required parameters
     */
    private function validateRequiredParams($soloContact, $dataLabsApi)
    {
        $requiredParams = [
            'first_name' => $soloContact->c_first_name,
            'last_name' => $soloContact->c_last_name,
            'postal_code' => $soloContact->leads->zip,
            'api_key' => $dataLabsApi['api_key'],
        ];

        foreach ($requiredParams as $key => $value) {
            if (empty($value)) {
                DB::table('contacts')->where('id', $soloContact->id)->update([
                    'verified_status' => 'Unverified - By Scrap Api',
                    'prospect_verified' => 'required_fields_for_datatlabs_missing:' . $key
                ]);

                return ['status' => false, 'data' => 'Missing parameter: ' . $key];
            }
        }

        return null;
    }

    /**
     * Make API request to DataLabs
     */
    private function makeApiRequest($soloContact, $dataLabsApi)
    {
        $url = $dataLabsApi['api_contact_search_url'];
        $query = [
            'first_name' => $soloContact->c_first_name,
            'last_name' => $soloContact->c_last_name,
            'postal_code' => $soloContact->leads->zip,
            'api_key' => $dataLabsApi['api_key'],
        ];

        return Http::get($url, $query);
    }

    /**
     * Handle successful API response
     */
    private function handleSuccessfulResponse($response, $soloContact, $platformId)
    {
        $filteredResponse = $this->filterContactsByPostalCode(
            $response->json()['data'],
            $soloContact->leads->zip
        );

        $this->updateWithDataLabsData($filteredResponse, $soloContact, $response, $platformId);

        return ['status' => true, 'data' => $filteredResponse];
    }

    /**
     * Handle failed API response
     */
    private function handleFailedResponse($response, $soloContact, $platformId)
    {
        $status = $this->verifyProspectStatus(
            $this->currentApiPriority,
            $this->maxApiPriority,
            $soloContact->prospect_verified,
            '',
            '',
            '',
            ''
        );

        DB::table('contacts')->where('id', $soloContact->id)->update([
            'verified_status' => 'Unverified - By Scrap Api',
            'prospect_verified' => $status
        ]);

        DB::table('scrap_contact_api_platforms')->insert([
            'contact_id' => $soloContact->id,
            'api_platform_id' => $this->apiPlatformId,
            'api_response' => $response,
            'status' => $status
        ]);

        return ['status' => false, 'data' => $response->json()['error']['message']];
    }

    /**
     * Filter contacts by postal code
     */
    private function filterContactsByPostalCode($data, $postalCode)
    {
        if (!isset($data['location_postal_code']) || $data['location_postal_code'] != $postalCode) {
            return [];
        }

        return [$data];
    }

    /**
     * Log error details
     */
    private function logError(\Throwable $err): void
    {
        \Log::error('Error in fetchDataFromDataLabs', [
            'message' => $err->getMessage(),
            'line' => $err->getLine(),
            'file' => $err->getFile(),
            'trace' => $err->getTraceAsString(),
        ]);
    }

    /**
     * Update contact with DataLabs data
     */
    public function updateWithDataLabsData($datalabsData, $soloContact, $response, $platformId)
    {
        if ($this->isEmptyData($datalabsData)) {
            $this->handleEmptyData($soloContact, $response, $platformId);
            return;
        }

        $this->handleValidData($datalabsData, $soloContact, $response, $platformId);
    }

    /**
     * Check if data is empty
     */
    private function isEmptyData($datalabsData): bool
    {
        return !is_array($datalabsData) || count($datalabsData) == 0;
    }

    /**
     * Handle empty data scenario
     */
    private function handleEmptyData($soloContact, $response, $platformId): void
    {
        $prospectVerified = $this->verifyProspectStatus(
            $this->currentApiPriority,
            $this->maxApiPriority,
            $soloContact->prospect_verified,
            '',
            '',
            '',
            ''
        );

        $this->updateContactWithData($soloContact->id, [], $prospectVerified);
        $this->insertApiPlatformRecord($soloContact->id, $platformId, $response, $prospectVerified);
    }

    /**
     * Handle valid data scenario
     */
    private function handleValidData($datalabsData, $soloContact, $response, $platformId): void
    {
        $data = $datalabsData[0];
        $contactData = $this->extractContactData($data, $soloContact);
        $prospectVerified = $this->verifyProspectStatus(
            $this->currentApiPriority,
            $this->maxApiPriority,
            $soloContact->prospect_verified,
            $contactData['address1'],
            $contactData['city'],
            $contactData['zip'],
            $contactData['email']
        );

        $this->updateContactWithData($soloContact->id, $contactData, $prospectVerified);
        $this->insertApiPlatformRecord($soloContact->id, $platformId, $response, $prospectVerified);
        $this->updateSocialProfiles($soloContact->id, $data);
    }

    /**
     * Extract contact data from API response
     */
    private function extractContactData($data, $soloContact): array
    {
        return [
            'email' => $this->getEmail($data, $soloContact),
            'secondaryEmail' => $this->getSecondaryEmail($data, $soloContact),
            'phone' => $this->getPhone($data, $soloContact),
            'secondaryPhone' => $this->getSecondaryPhone($data, $soloContact),
            'address1' => $data['location_street_address'] ?? $soloContact->c_address1 ?? '',
            'city' => $data['location_locality'] ?? $soloContact->c_city ?? '',
            'zip' => $data['location_postal_code'] ?? $soloContact->c_zip ?? '',
            'state' => $this->getState($data, $soloContact),
        ];
    }

    /**
     * Get primary email
     */
    private function getEmail($data, $soloContact)
    {
        $recommended = $data['recommended_personal_email'] ?? '';
        $emails = $data['personal_emails'] ?? [];

        if (empty($emails)) {
            return $soloContact->c_email ?? '';
        }

        $prioritized = $this->prioritizeEmails($emails, $recommended);
        return $prioritized[0] ?? $soloContact->c_email ?? '';
    }

    /**
     * Get secondary email
     */
    private function getSecondaryEmail($data, $soloContact)
    {
        $recommended = $data['recommended_personal_email'] ?? '';
        $emails = $data['personal_emails'] ?? [];

        if (empty($emails)) {
            return $soloContact->c_secondary_email ?? '';
        }

        $prioritized = $this->prioritizeEmails($emails, $recommended);
        return $prioritized[1] ?? $soloContact->c_secondary_email ?? '';
    }

    /**
     * Get primary phone
     */
    private function getPhone($data, $soloContact)
    {
        $mobile = $data['mobile_phone'] ?? '';
        $phones = $data['phone_numbers'] ?? [];

        if (empty($phones)) {
            return $soloContact->c_phone ?? '';
        }

        $prioritized = $this->prioritizePhones($mobile, $phones);
        return $prioritized[0] ?? $soloContact->c_phone ?? '';
    }

    /**
     * Get secondary phone
     */
    private function getSecondaryPhone($data, $soloContact)
    {
        $mobile = $data['mobile_phone'] ?? '';
        $phones = $data['phone_numbers'] ?? [];

        if (empty($phones)) {
            return $soloContact->c_secondary_phone ?? '';
        }

        $prioritized = $this->prioritizePhones($mobile, $phones);
        return $prioritized[1] ?? $soloContact->c_secondary_phone ?? '';
    }

    /**
     * Get state
     */
    private function getState($data, $soloContact)
    {
        $region = $data['location_region'] ?? '';
        if (!empty($region) && in_array(strtolower($region), ['florida'])) {
            return ucfirst(strtolower($region));
        }
        return $soloContact->c_state ?? '';
    }

    /**
     * Update contact with data
     */
    private function updateContactWithData($contactId, array $data, $prospectVerified): void
    {
        DB::table('contacts')->updateOrInsert(['id' => $contactId], [
            'verified_status' => 'Unverified - By Scrap Api',
            'prospect_verified' => $prospectVerified,
            'c_zip' => $data['zip'] ?? '',
            'c_city' => $data['city'] ?? '',
            'c_secondary_phone' => $data['secondaryPhone'] ?? '',
            'c_phone' => $data['phone'] ?? '',
            'c_email' => $data['email'] ?? '',
            'c_secondary_email' => $data['secondaryEmail'] ?? '',
            'c_address1' => $data['address1'] ?? '',
            'c_state' => $data['state'] ?? '',
        ]);
    }

    /**
     * Insert API platform record
     */
    private function insertApiPlatformRecord($contactId, $platformId, $response, $status): void
    {
        DB::table('scrap_contact_api_platforms')->insert([
            'contact_id' => $contactId,
            'api_platform_id' => $platformId,
            'api_response' => $response,
            'status' => $status
        ]);
    }

    /**
     * Update social profiles
     */
    private function updateSocialProfiles($contactId, $data): void
    {
        $otherInfos = [
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'linkedin_username' => $data['linkedin_username'] ?? null,
            'linkedin_id' => $data['linkedin_id'] ?? null,
            'facebook_url' => $data['facebook_url'] ?? null,
            'facebook_username' => $data['facebook_username'] ?? null,
            'facebook_id' => $data['facebook_id'] ?? null,
            'twitter_url' => $data['twitter_url'] ?? null,
            'twitter_username' => $data['twitter_username'] ?? null,
            'github_url' => $data['github_url'] ?? null,
            'github_username' => $data['github_username'] ?? null,
        ];

        $notNullData = array_filter($otherInfos, function ($value) {
            return $value !== null && $value !== '';
        });

        if (count($notNullData) > 0) {
            DB::table('scrap_contact_social_profile')->updateOrInsert(
                ['contact_id' => $contactId],
                $otherInfos
            );
        }
    }

    /**
     * Prioritize emails by domain
     */
    public function prioritizeEmails($emails, $recommendedPersonalEmail)
    {
        $acceptedProviders = ['gmail', 'yahoo', 'outlook', 'hotmail', 'aol', 'worldnet.att.net'];
        $prioritizedEmails = [];

        foreach ($acceptedProviders as $provider) {
            foreach ($emails as $email) {
                $domain = substr(strrchr($email, '@'), 1);
                if ($domain && stripos($domain, $provider) !== false) {
                    $prioritizedEmails[] = $email;
                }
            }
        }

        if (!empty($recommendedPersonalEmail)) {
            $secondEmail = $prioritizedEmails[0] ?? '';
            return $recommendedPersonalEmail === $secondEmail
                ? [$recommendedPersonalEmail, '']
                : [$recommendedPersonalEmail, $secondEmail];
        }

        $firstEmail = $prioritizedEmails[0] ?? '';
        $secondEmail = $prioritizedEmails[1] ?? '';
        return $firstEmail === $secondEmail ? [$firstEmail, ''] : [$firstEmail, $secondEmail];
    }

    /**
     * Remove +1 prefix from phone number
     */
    public function removePlusOne($number)
    {
        return preg_replace('/^\+1/', '', $number);
    }

    /**
     * Prioritize phone numbers
     */
    public function prioritizePhones($mobile, $otherPhoneNumbers)
    {
        if ($mobile) {
            $mobile = $this->removePlusOne($mobile);
        }

        foreach ($otherPhoneNumbers as &$number) {
            $number = $this->removePlusOne($number);
        }

        $primary = $mobile ?: ($otherPhoneNumbers[0] ?? null);
        $secondary = null;

        if ($mobile) {
            $secondary = $otherPhoneNumbers[0] ?? null;
        } else {
            $secondary = $otherPhoneNumbers[1] ?? $otherPhoneNumbers[0] ?? null;
        }

        if ($primary === $secondary) {
            $secondary = '';
        }

        return [$primary, $secondary];
    }
}
