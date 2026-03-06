<?php

namespace App\Services;

use App\Traits\CommonFunctionsTrait;
use Goutte\Client;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;

class GetSunBizDetailsBasic
{
    use CommonFunctionsTrait;

    // Status constants
    private const STATUS_ACTIVE = 'Active';
    private const STATUS_SELECTED = 'selected';
    private const STATUS_NOT_SELECTED = 'not_selected';

    // Entity types for similarity matching
    private const ENTITY_TYPES = ['CONDOMINIUM', 'ASSOCIATION', 'INC', 'LLC', 'LIMITED'];

    // Minimum similarity threshold
    private const MIN_SIMILARITY = 0.5;

    /**
     * Get replacement mappings part 1
     */
    protected function getReplacementsPart1(): array
    {
        return [
            ' ASSOC ' => ' ASSOCIATION ',
            ' ASSC ' => ' ASSOCIATION ',
            ' ASSN ' => ' ASSOCIATION ',
            ' APRTMNTS ' => ' APPARTMENTS ',
            ' AVE ' => ' AVENUE ',
            ' BCH ' => ' BEACH ',
            ' BLDG ' => ' BUILDING ',
            ' CLB ' => ' CLUB ',
            ' COMM ' => ' COMMERCIAL ',
            ' CMNTY ' => ' COMMUNITY ',
            ' CONDO ' => ' CONDOMINIUM ',
            ' CNDO ' => ' CONDOMINIUM ',
            ' CONDOS ' => ' CONDOMINIUM ',
            ' CNDMMS ' => ' CONDOMINIUM ',
            ' CLRWTER ' => ' CLEARWATER ',
            ' CTR ' => ' CENTER ',
            ' DSTIN ' => ' DISTINCT ',
        ];
    }

    /**
     * Get replacement mappings part 2
     */
    protected function getReplacementsPart2(): array
    {
        return [
            ' POA ' => " PROPERTY OWNERS' ASSOCIATION ",
            ' PROF ' => ' PROFESSIONAL ',
            ' PRFSSNL ' => ' PROFESSIONAL ',
            ' PRTNERS ' => ' PARTNERS ',
            ' STN ' => ' STATION ',
            ' ST ' => ' STREET ',
            ' SNSHINE ' => ' SUNSHINE ',
            ' TWERS ' => ' TOWERS ',
            ' HLMES ' => ' HOLMES ',
            ' MED ' => ' MEDICAL ',
            ' CLNY ' => ' COLONY ',
            ' MASTER ' => ' MASTER ',
            ' HSE ' => ' HOUSE ',
            ' HSES ' => ' HOUSES ',
            ' HMOWNERS ' => ' HOMEOWNERS ',
            ' BRCKELL ' => ' BRICKELL ',
        ];
    }

    /**
     * Get replacement mappings part 3
     */
    protected function getReplacementsPart3(): array
    {
        return [
            ' BLVD ' => ' Boulevard ',
            ' DRV ' => ' DRIVE ',
            ' VLG ' => ' VILLAGE ',
            ' LK ' => ' LAKE ',
            ' MNGROVE ' => ' MANGROVE ',
            ' ASSOCIATES ' => ' ASSOCIATION ',
            ' HBR ' => ' HARBOR ',
            ' EGLE ' => ' EAGLE ',
            ' PT ' => ' POINT ',
            ' PNTE ' => ' POINTE ',
            ' VDRA ' => ' VEDRA ',
            ' RSORT ' => ' RESORT ',
            ' CNTRY ' => ' COUNTRY ',
            ' CORP ' => ' CORPORATION ',
            ' ADM ' => ' ADMINISTRATIVE ',
            ' MGT ' => ' MANAGEMENT ',
        ];
    }

    /**
     * Get replacement mappings part 4
     */
    protected function getReplacementsPart4(): array
    {
        return [
            ' PK ' => ' PARK ',
            ' FREST ' => ' FOREST ',
            ' FLMING ' => ' FLEMING ',
            ' TWNHSES ' => ' TOWNHOUSES ',
            ' CCO ' => ' COCOA ',
            ' GRDNS ' => ' GARDENS ',
            ' SCTION ' => ' SECTION ',
            ' RSDNCE ' => ' RESIDENCE ',
            ' PL ' => ' PLACE ',
            ' TNEY ' => ' TONEY ',
            ' PNNA ' => ' PENNA ',
            ' HTS ' => ' HEIGHTS ',
            ' VNDRBILT ' => ' VANDERBILT ',
            ' SMNOLE ' => ' SEMINOLE ',
            ' TSCANY ' => ' TUSCANY ',
            ' COML ' => ' COMMERCIAL ',
        ];
    }

    /**
     * Get replacement mappings part 5
     */
    protected function getReplacementsPart5(): array
    {
        return [
            ' S ' => ' SOUTH ',
            ' ORNGE ' => ' ORANGE ',
            ' SNST ' => ' SUNSET ',
            ' NE ' => ' NEIGHBORHOOD ',
            ' CNWAY ' => ' CONWAY ',
            ' WODS ' => ' WOODS ',
            ' LNDS ' => ' LANDS ',
            ' PR ' => ' PRESIDENT ',
            ' TR ' => ' TERRACE ',
            ' FRTY ' => ' FORTY ',
            ' PRPRTY ' => ' PROPERTIES ',
            ' BOCA W ' => ' BOCA WEST ',
            ' RE ' => ' REAL ESTATE ',
            ' HNTERS ' => ' HUNTERS ',
            ' JPITER ' => ' JUPITER ',
            ' MGNLIA ' => ' MAGNOLIA ',
        ];
    }

    /**
     * Get replacement mappings part 6
     */
    protected function getReplacementsPart6(): array
    {
        return [
            ' SQ ' => ' SQUARE ',
            ' MAMI ' => ' MIAMI ',
            ' HRITG ' => ' HERITAGE ',
            ' DGLAS ' => ' DOUGLAS ',
            ' RDGE ' => ' RIDGE ',
            ' SRSOTA ' => ' SARASOTA ',
            ' TURNBRRY ' => ' TURNBERRY ',
            ' DNES ' => ' DUNES ',
            ' RVGOLF ' => ' R.V./GOLF ',
            ' E ' => ' EAST ',
            ' W ' => ' WEST ',
            ' RCRTL ' => ' RECREATIONAL ',
            ' VHCL ' => ' VEHICLE ',
            ' PRKG ' => ' PARKING ',
            ' CMMRCE ' => ' COMMERCE ',
            ' BUS ' => ' BUSINESS ',
        ];
    }

    /**
     * Get all replacement mappings merged together
     */
    protected function getAllReplacements(): array
    {
        return array_merge(
            $this->getReplacementsPart1(),
            $this->getReplacementsPart2(),
            $this->getReplacementsPart3(),
            $this->getReplacementsPart4(),
            $this->getReplacementsPart5(),
            $this->getReplacementsPart6()
        );
    }

    /**
     * Replace substrings in string based on mapping
     */
    public function replaceSubstrings($string)
    {
        $replacements = $this->getAllReplacements();
        $string = $string . ' ';

        foreach ($replacements as $search => $replace) {
            $string = str_replace($search, $replace, $string);
        }

        return trim($string);
    }

    /**
     * Scrape Sunbiz for business data
     */
    public function scrapeSunbiz($leadName = 'Ocean 14')
    {
        $upperName = strtoupper($leadName);
        $entityName = str_replace(' ', '%20', $upperName);
        $searchNameOrder = strtoupper(str_replace(' ', '', $upperName));

        $listUrl = 'https://search.sunbiz.org/Inquiry/CorporationSearch/SearchResults/EntityName/' . $entityName . '/Page1?searchNameOrder=' . $searchNameOrder;

        $html = $this->fetchHtml($listUrl);

        if (empty($html)) {
            return [];
        }

        return $this->parseSearchResults($html, $leadName, $listUrl);
    }

    /**
     * Fetch HTML content from URL using Browsershot
     */
    private function fetchHtml(string $url): string
    {
        try {
            return Browsershot::url($url)
                ->timeout(60)
                ->waitUntilNetworkIdle()
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
                ->bodyHtml();
        } catch (\Exception $e) {
            \Log::error('Browsershot failed for URL: ' . $url . ' | ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Parse search results and find matching business
     */
    private function parseSearchResults(string $html, string $leadName, string $listUrl): array
    {
        $crawler = new Crawler($html);
        $scrapResponse = [];
        $matchFound = false;

        $crawler->filter('[id^="search-results"]>table>tbody>tr')->each(
            function ($node) use (&$scrapResponse, &$matchFound, $leadName, $listUrl) {
                $status = $node->filter('.small-width')->text();

                if ($status != self::STATUS_ACTIVE) {
                    return;
                }

                $businessName = $node->filter('.large-width')->text();
                $businessNameHref = $node->filter('.large-width > a')->attr('href');

                $similarity = $this->calculateSimilarity($leadName, $businessName);

                if ($similarity['similarity'] >= self::MIN_SIMILARITY && !$matchFound) {
                    $selectionStatus = ($leadName === $businessName) ? self::STATUS_SELECTED : self::STATUS_NOT_SELECTED;
                    $scrapResponse['handle_data'] = $this->getContactDetails($businessNameHref, $listUrl);
                    $matchFound = true;
                }
            }
        );

        return $scrapResponse;
    }

    /**
     * Calculate similarity between lead name and business name
     */
    public function calculateSimilarity(string $leadName, string $businessName): array
    {
        $originalBusinessName = $businessName;

        $normalizedLead = $this->normalizeString($leadName);
        $normalizedBusiness = $this->normalizeString($businessName);

        $similarity = $this->computeSimilarity($normalizedLead, $normalizedBusiness);

        echo 'similarity' . $similarity;

        return ['similarity' => $similarity, 'business_name' => $originalBusinessName];
    }

    /**
     * Normalize string for comparison
     */
    private function normalizeString(string $str): string
    {
        return strtolower(str_replace([' ', '(', ')', '.', ',', '\''], '', $str));
    }

    /**
     * Compute similarity score between two strings
     */
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
     * Get contact details from Sunbiz detail page
     */
    public function getContactDetails(string $url, string $listUrl): array
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

        return $this->processContactData($crawler, $data, $membersNames, $finalArr);
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

    /**
     * Extract span data from crawler and populate final array
     */
    private function extractSpanData(Crawler $crawler, array &$finalArr): array
    {
        $spans = $crawler->filter('div.detailSection > span');
        $data = [];

        $spanCount = $spans->count();
        for ($i = 0; $i < $spanCount; $i++) {
            $text = trim($spans->eq($i)->text());
            $data[] = $text;
            $this->processSpanText($text, $i, $spans, $finalArr);
        }

        return $data;
    }

    /**
     * Process span text based on content type
     */
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

    /**
     * Parse address from HTML div element
     */
    private function parseAddress(Crawler $addressDiv): string
    {
        $rawHtml = $addressDiv->html();
        $addressLines = array_filter(array_map('trim',
            preg_split('/<br[^>]*>/i', strip_tags($rawHtml, '<br>'))
        ));

        return implode(' ', $addressLines);
    }

    /**
     * Extract member names from detail sections
     */
    private function extractMemberNames(Crawler $crawler): array
    {
        $membersNames = [];
        $sections = $crawler->filter('.detailSection');

        foreach ($sections as $section) {
            $sectionCrawler = new Crawler($section);
            $sectionCrawler->filterXPath('//text()[normalize-space()]')->each(
                function ($node) use (&$membersNames) {
                    $val = trim($node->text());
                    if (!empty($val)) {
                        $membersNames[] = $val;
                    }
                }
            );
        }

        return $membersNames;
    }

    /**
     * Process contact data and extract members
     */
    private function processContactData(Crawler $crawler, array $data, array $membersNames, array $finalArr): array
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
            $finalArr['members'] = $this->populateMemberNames($members, $membersNames);
        }

        return $finalArr;
    }

    /**
     * Extract members array from data
     */
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

    /**
     * Populate member names from extracted names array
     */
    private function populateMemberNames(array $members, array $membersNames): array
    {
        for ($i = 0; $i < count($members); $i++) {
            $parsedName = $this->parseMemberName($membersNames[$i]);
            $members[$i]['member_name'] = $parsedName['full_name'];
            $members[$i]['first_name'] = $parsedName['first_name'];
            $members[$i]['last_name'] = $parsedName['last_name'];
        }

        return $members;
    }

    /**
     * Parse member name into first and last name
     */
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
