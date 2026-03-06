<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use App\Services\GetSunBizDetailsBasic;
use App\Traits\SunbizDataTrait;
use DB;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Command to scrape business data from Sunbiz website
 */
class ScrapeSunbizData extends Command
{
    use SunbizDataTrait;

    /** @var string Command signature */
    protected $signature = 'scrape:sunbiz';

    /** @var string Command description */
    protected $description = 'Scrape data from the Sunbiz website';

    // Status constants
    private const STATUS_ACTIVE = 'Active';
    private const STATUS_CRAWLED = 'crawled';
    private const STATUS_FAILED_CRAWL = 'failedcrawl';

    // Entity types for similarity matching
    private const ENTITY_TYPES = ['CONDOMINIUM', 'ASSOCIATION', 'INC', 'LLC', 'LIMITED'];

    // Minimum similarity threshold
    private const MIN_SIMILARITY = 0.5;

    /**
     * Execute the console command
     */
    public function handle()
    {
        $pendingBusinesses = $this->fetchPendingBusinesses();

        if ($pendingBusinesses->isEmpty()) {
            return $this->info('No pending businesses found.');
        }

        return $this->runScrapeLoop($pendingBusinesses);
    }

    /**
     * Fetch pending businesses for scraping
     */
    private function fetchPendingBusinesses()
    {
        return Lead::where('is_added_by_bot', 1)
            ->where('id', '>', 26206)
            ->orderBy('id', 'asc')
            ->select('id', 'name')
            ->get();
    }

    /**
     * Run the scrape loop for all pending businesses
     */
    private function runScrapeLoop($pendingBusinesses)
    {
        $count = $this->processBusinessesLoop($pendingBusinesses);

        return $this->info($count . ' businesses has been updated successfully.');
    }

    /**
     * Process businesses in loop
     */
    private function processBusinessesLoop($pendingBusinesses)
    {
        $count = 0;
        $getSunBiz = new GetSunBizDetailsBasic;

        foreach ($pendingBusinesses as $business) {
            $this->processSingleBusiness($business, $getSunBiz);
            $count++;
            Log::channel('scrap_sunbiz')->info(' updated successfully.');
        }

        return $count;
    }

    /**
     * Process a single business
     */
    private function processSingleBusiness($business, GetSunBizDetailsBasic $getSunBiz)
    {
        echo 'Business Id = ' . $business->id;
        $leadName = $getSunBiz->replaceSubstrings(strtoupper($business->name));
        $this->scrapeSunbiz($leadName, $business->id);
    }

    /**
     * Scrape Sunbiz for business data
     */
    public function scrapeSunbiz($leadName, $leadId)
    {
        $upperName = strtoupper($leadName);
        $entityName = str_replace(' ', '%20', $upperName);
        $searchNameOrder = strtoupper(str_replace(' ', '', $upperName));
        $listUrl = $this->buildSearchUrl($entityName, $searchNameOrder);

        return $this->executeScrape($listUrl, $leadId, $leadName);
    }

    /**
     * Build the search URL for Sunbiz
     */
    private function buildSearchUrl(string $entityName, string $searchNameOrder): string
    {
        return 'https://search.sunbiz.org/Inquiry/CorporationSearch/SearchResults/EntityName/'
            . $entityName . '/Page1?searchNameOrder=' . $searchNameOrder;
    }

    /**
     * Execute the scrape process
     */
    private function executeScrape(string $listUrl, int $leadId, string $leadName)
    {
        $html = $this->fetchHtml($listUrl, $leadId);
        if (empty($html)) {
            return [];
        }

        return $this->parseSearchResults($html, $leadName, $leadId, $listUrl);
    }

    /**
     * Fetch HTML content from URL using Browsershot
     */
    private function fetchHtml(string $url, int $leadId): string
    {
        try {
            return Browsershot::url($url)
                ->timeout(60)
                ->waitUntilNetworkIdle()
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
                ->bodyHtml();
        } catch (\Exception $e) {
            Log::error('Browsershot failed for URL: ' . $url . ' | ' . $e->getMessage());
            $this->updateLeadStatus($leadId, self::STATUS_FAILED_CRAWL);

            return '';
        }
    }

    // Parse search results and find matching business
    private function parseSearchResults(string $html, string $leadName, int $leadId, string $listUrl): array
    {
        $crawler = new Crawler($html);
        $scrapResponse = [];
        $matchFound = false;

        $crawler->filter('[id^="search-results"]>table>tbody>tr')->each(
            function ($node) use (&$scrapResponse, &$matchFound, $leadName, $leadId, $listUrl) {
                $status = $node->filter('.small-width')->text();

                if ($status != self::STATUS_ACTIVE) {
                    return;
                }

                $businessName = $node->filter('.large-width')->text();
                $businessNameHref = $node->filter('.large-width > a')->attr('href');

                $similarity = $this->calculateSimilarity($leadName, $businessName);

                if ($similarity['similarity'] >= self::MIN_SIMILARITY && !$matchFound) {
                    $scrapResponse['handle_data'] = $this->getContactDetails(
                        $businessNameHref,
                        $listUrl,
                        $leadId
                    );

                    $detailsUrl = 'https://search.sunbiz.org' . $businessNameHref;
                    Lead::where('id', $leadId)->update([
                        'sunbiz_list_url' => $listUrl,
                        'sunbiz_details_url' => $detailsUrl,
                        'sunbiz_status' => self::STATUS_CRAWLED,
                    ]);
                    $matchFound = true;
                }
            }
        );

        if (!$matchFound) {
            Lead::where('id', $leadId)->update([
                'sunbiz_list_url' => $listUrl,
                'sunbiz_details_url' => '',
                'sunbiz_status' => self::STATUS_FAILED_CRAWL,
            ]);
        }

        return $scrapResponse;
    }

    // Calculate similarity between lead name and business name
    public function calculateSimilarity(string $leadName, string $businessName): array
    {
        $originalBusinessName = $businessName;

        $normalizedLead = $this->normalizeString($leadName);
        $normalizedBusiness = $this->normalizeString($businessName);

        $similarity = $this->computeSimilarity($normalizedLead, $normalizedBusiness);

        return ['similarity' => $similarity, 'business_name' => $originalBusinessName];
    }

    private function normalizeString(string $str): string
    {
        return strtolower(str_replace([' ', '(', ')', '.', ',', '\''], '', $str));
    }

    // Compute similarity score between two strings
    private function computeSimilarity(string $leadName, string $businessName): float
    {
        $similarity = 0;

        if (strpos($businessName, $leadName) !== false) {
            $similarity += 0.5;
        }

        foreach (self::ENTITY_TYPES as $entity) {
            if (strpos($businessName, strtolower($entity)) !== false) {
                $similarity += 0.1;
            }
        }

        return $similarity;
    }

    /**
     * Update lead status in database
     */
    private function updateLeadStatus(int $leadId, string $status): void
    {
        Lead::where('id', $leadId)->update(['sunbiz_status' => $status]);
    }

    /**
     * Get contact details from Sunbiz detail page
     */
    public function getContactDetails(string $url, string $listUrl, int $leadId): array
    {
        $fullUrl = 'https://search.sunbiz.org' . $url;
        $client = new Client;
        $crawler = $client->request('GET', $fullUrl);

        if (!$crawler) {
            return [];
        }

        $finalArr = $this->initContactDetailsArray($listUrl, $fullUrl);
        $data = $this->extractSpanData($crawler, $finalArr);
        $membersNames = $this->extractMemberNames($crawler);

        return $this->processContactData($crawler, $data, $membersNames, $leadId, $finalArr);
    }

    /**
     * Initialize contact details array with default values
     */
    private function initContactDetailsArray(string $listUrl, string $fullUrl): array
    {
        return [
            'list_url' => $listUrl,
            'details_url' => $fullUrl,
            'principal_address' => null,
            'mailing_address' => null,
            'registered_name' => '',
            'registered_address' => '',
            'members' => [],
        ];
    }

    // Extract span data from crawler and populate final array
    private function extractSpanData(Crawler $crawler, array &$finalArr): array
    {
        $spans = $crawler->filter('div.detailSection > span');
        $data = [];

        for ($i = 0; $i < $spans->count(); $i++) {
            $text = trim($spans->eq($i)->text());
            $data[] = $text;
            $this->processSpanText($text, $i, $spans, $finalArr);
        }

        return $data;
    }

    // Process span text based on content type
    private function processSpanText(string $text, int $i, Crawler $spans, array &$finalArr): void
    {
        switch ($text) {
            case 'Mailing Address':
                $finalArr['mailing_address'] = trim($spans->eq($i + 1)->text());
                break;

            case 'Registered Agent Name & Address':
                $finalArr['registered_name'] = trim($spans->eq($i + 1)->text());
                $addressDiv = $spans->eq($i + 2)->filter('div');
                if ($addressDiv->count()) {
                    $finalArr['registered_address'] = $this->parseAddress($addressDiv);
                }
                break;
            default:
                $finalArr['principal_address'] = trim($spans->eq($i + 1)->text());
                break;
        }
    }

    // Parse address from HTML div element
    private function parseAddress(Crawler $addressDiv): string
    {
        $rawHtml = $addressDiv->html();
        $addressLines = array_filter(array_map('trim',
            preg_split('/<br[^>]*>/i', strip_tags($rawHtml, '<br>'))
        ));

        return implode(' ', $addressLines);
    }

    // Extract member names from detail sections
    private function extractMemberNames(Crawler $crawler): array
    {
        $membersNames = [];
        $sections = $crawler->filter('.detailSection');

        foreach ($sections as $section) {
            $sectionCrawler = new Crawler($section);
            $sectionCrawler->filterXPath('//text()[normalize-space()]')->each(
                function ($node) use (&$membersNames) {
                    $membersNames[] = trim($node->text());
                }
            );
        }

        return $membersNames;
    }

    // Process contact data and extract members
    private function processContactData(Crawler $crawler, array $data, array $membersNames, int $leadId, array $finalArr): array
    {
        $officerIndex = array_search('Officer/Director Detail', $data);
        if ($officerIndex === false) {
            $officerIndex = array_search('Authorized Person(s) Detail', $data);
        }

        $nameAddrIndex = array_search('Name & Address', $data);

        if ($officerIndex === false || $nameAddrIndex === false) {
            return $finalArr;
        }

        $members = $this->extractMembers($data, $officerIndex);

        if (count($members) && count($membersNames) && count($members) === count($membersNames)) {
            $finalArr['members'] = $this->saveMembers($members, $membersNames, $leadId);
        }

        return $finalArr;
    }

    // Extract members array from data
    private function extractMembers(array $data, int $officerIndex): array
    {
        $members = [];
        $readIndex = $officerIndex + 2;
        $dataCount = count($data);

        while ($readIndex < $dataCount) {
            if ($data[$readIndex] === 'Annual Reports') {
                break;
            }

            $title = isset($data[$readIndex])
                ? preg_replace('/^Title\s*/', '', $data[$readIndex])
                : '';
            $address = $data[$readIndex + 1] ?? '';

            $members[] = [
                'member_title' => trim($title),
                'member_address' => trim($address),
            ];

            $readIndex += 2;
        }

        return $members;
    }

    // Save members to database
    private function saveMembers(array $members, array $membersNames, int $leadId): array
    {
        for ($i = 0; $i < count($members); $i++) {
            $parsedName = $this->parseMemberName($membersNames[$i]);

            $members[$i]['member_name'] = $parsedName['full_name'];
            $members[$i]['first_name'] = $parsedName['first_name'];
            $members[$i]['last_name'] = $parsedName['last_name'];

            // Check and update/create contact
            $this->checkContactExistance(
                $leadId,
                $parsedName['first_name'],
                $parsedName['last_name'],
                $parsedName['full_name'],
                $members[$i]['member_title']
            );

            DB::table('contactscraps')->insert([
                'c_full_name' => $parsedName['full_name'],
                'c_title' => $members[$i]['member_title'],
                'lead_id' => $leadId,
                'c_first_name' => $parsedName['first_name'],
                'c_last_name' => $parsedName['last_name'],
                'added_by_scrap_apis' => 1,
            ]);
        }

        return $members;
    }

    // Parse member name into first and last name
    private function parseMemberName(string $name): array
    {
        $firstName = $name;
        $lastName = '';

        if (strpos($name, ',') !== false) {
            $parts = preg_split('/,\s*/', $name);
            $firstName = end($parts);
            $lastName = implode(' ', array_slice($parts, 0, -1));
        }

        $fullName = trim($firstName . ' ' . $lastName);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
        ];
    }
}
