<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use App\Model\ScrapCity;
use App\Services\GetLangLongGoogleService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Command to scrape businesses from Yellow Pages with new logic
 */
class ScrapYellowPagesNew extends Command
{
    /** @var string Command signature */
    protected $signature = 'command:scrap-yellow-pages-new';

    /** @var string Command description */
    protected $description = 'It will scrap the businesses from yellow pages and push in the database';

    // Base URL
    private const BASE_URL = 'https://www.yellowpages.com/search';

    // Processing constants
    private const STATUS_PENDING = 1;
    private const STATUS_SUCCESS = 2;
    private const STATUS_FAILED = 3;
    private const CHUNK_SIZE = 5;
    private const TIMEOUT = 60;

    // Google Service instance
    private $googleService;

    public function __construct()
    {
        parent::__construct();
        $this->googleService = new GetLangLongGoogleService;
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        Log::channel('yellowpagesscrap')->info('Cron started at ' . Carbon::now());

        $this->processSearchCollection();

        Log::channel('yellowpagesscrap')->info('Cron stopped at ' . Carbon::now() . "\n");
    }

    /**
     * Process search collection in chunks
     */
    private function processSearchCollection(): void
    {
        ScrapCity::with('scrapCounty')
            ->where('status', self::STATUS_PENDING)
            ->chunk(self::CHUNK_SIZE, function ($searchCollection) {
                foreach ($searchCollection as $search) {
                    $this->processSingleSearch($search);
                }
            });
    }

    /**
     * Process a single search
     */
    private function processSingleSearch($search): void
    {
        $businessData = $this->scrapeBusinessData($search);
        $this->saveBusinessData($businessData, $search);
        $this->cleanupMemory();
    }

    /**
     * Scrape business data from Yellow Pages
     */
    private function scrapeBusinessData($search): array
    {
        $data = [];
        $paginate = 1;
        $paginateTotal = 0;

        do {
            $url = $this->buildSearchUrl($search, $paginate);
            $html = $this->fetchHtml($url);

            if (empty($html)) {
                break;
            }

            $crawler = new Crawler($html);
            $paginateTotal = $this->calculatePaginationTotal($crawler, $paginateTotal);
            $data = array_merge($data, $this->extractBusinessData($crawler, $search));

            $paginate++;
        } while ($paginate <= $paginateTotal);

        return $data;
    }

    /**
     * Build search URL
     */
    private function buildSearchUrl($search, int $paginate): string
    {
        $url = self::BASE_URL . '?search_terms='
            . $search->search_keyword . '&geo_location_terms='
            . $search->city . '%2C%20' . $search->state_code;

        if ($paginate > 1) {
            $url .= '&page=' . $paginate;
        }

        return $url;
    }

    /**
     * Fetch HTML using Browsershot
     */
    private function fetchHtml(string $url): string
    {
        try {
            return Browsershot::url($url)
                ->timeout(self::TIMEOUT)
                ->waitUntilNetworkIdle()
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
                ->bodyHtml();
        } catch (\Exception $e) {
            Log::error('Browsershot failed for URL: ' . $url . ' | ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Calculate pagination total
     */
    private function calculatePaginationTotal(Crawler $crawler, int $currentTotal): int
    {
        if ($currentTotal > 0) {
            return $currentTotal;
        }

        try {
            $paginationStr = $crawler->filter('[class^="showing-count"]')->text();
            $pieces = explode(' ', $paginationStr);

            $total = (int) $pieces[3];
            $each = (int) explode('-', $pieces[1])[1];

            return ceil($total / $each);
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Extract business data from crawler
     */
    private function extractBusinessData(Crawler $crawler, $search): array
    {
        $data = [];

        $crawler->filter('[class^="result"][id^="lid-"]')->each(function ($node) use (&$data, $search) {
            $data[] = $this->extractSingleBusiness($node, $search);
        });

        return $data;
    }

    /**
     * Extract single business data
     */
    private function extractSingleBusiness($node, $search): array
    {
        $streetAddress = $this->getStreetAddress($node);
        $locality = $this->getLocality($node);
        $parsedLocality = $this->parseLocality($locality);
        $latLong = $this->getLatLong($streetAddress, $parsedLocality);

        return [
            'type' => $search->search_keyword,
            'businessName' => $this->getBusinessName($node),
            'phone' => $this->getPhone($node),
            'address1' => $streetAddress,
            'address2' => '',
            'city' => $parsedLocality['city'],
            'zip' => $parsedLocality['zip'],
            'state' => $parsedLocality['state'],
            'locality' => $locality,
            'latitude' => $latLong['lat'],
            'longitude' => $latLong['long'],
            'county' => $search->scrapCounty->name ?? '',
            'county_id' => $search->county_id,
        ];
    }

    /**
     * Get business name from node
     */
    private function getBusinessName($node): string
    {
        $name = $node->filter('h2.n')->count() ? $node->filter('h2.n')->text() : '';
        return preg_replace('/^\d+\.\s*/', '', $name);
    }

    /**
     * Get phone from node
     */
    private function getPhone($node): string
    {
        return $node->filter('.phones.phone.primary')->count()
            ? $node->filter('.phones.phone.primary')->text()
            : '';
    }

    /**
     * Get street address from node
     */
    private function getStreetAddress($node): string
    {
        return $node->filter('.street-address')->count()
            ? $node->filter('.street-address')->text()
            : '';
    }

    /**
     * Get locality from node
     */
    private function getLocality($node): string
    {
        return $node->filter('.locality')->count()
            ? $node->filter('.locality')->text()
            : '';
    }

    /**
     * Parse locality into city, state, zip
     */
    private function parseLocality(string $locality): array
    {
        preg_match('/([^,]+),\s*(\w+)\s+(\d+)/', $locality, $matches);

        return [
            'city' => $matches[1] ?? '',
            'state' => $matches[2] ?? '',
            'zip' => $matches[3] ?? '',
        ];
    }

    /**
     * Get latitude and longitude
     */
    private function getLatLong(string $streetAddress, array $parsedLocality): array
    {
        if (empty($streetAddress)) {
            return ['lat' => null, 'long' => null];
        }

        $fullAddress = $this->buildFullAddress($streetAddress, $parsedLocality);
        $latLong = $this->googleService->getLatLngFromGoogleService($fullAddress);

        return [
            'lat' => $latLong['lat'] ?? null,
            'long' => $latLong['long'] ?? null,
        ];
    }

    /**
     * Build full address string
     */
    private function buildFullAddress(string $streetAddress, array $parsedLocality): string
    {
        $address = $streetAddress;

        if (!empty($parsedLocality['city'])) {
            $address .= ' ' . $parsedLocality['city'] . ',';
        }
        if (!empty($parsedLocality['state'])) {
            $address .= ' ' . $parsedLocality['state'] . ',';
        }
        if (!empty($parsedLocality['zip'])) {
            $address .= ' ' . $parsedLocality['zip'] . ',';
        }

        return $address;
    }

    /**
     * Save business data
     */
    private function saveBusinessData(array $data, $search): void
    {
        $updatedStatus = empty($data) ? self::STATUS_FAILED : self::STATUS_SUCCESS;

        if ($updatedStatus === self::STATUS_SUCCESS) {
            $lead = new Lead;
            $scrapStatus = $lead->evaluateCrawlerLeads($data, $search->search_keyword);

            Log::channel('yellowpagesscrap')->info(
                'Scrap status of ' . json_encode($search->toArray())
            );
        }

        $this->updateSearchStatus($search->id, $updatedStatus);
    }

    /**
     * Update search status
     */
    private function updateSearchStatus(int $searchId, int $status): void
    {
        DB::table('scrap_cities')
            ->where('id', $searchId)
            ->update(['status' => $status]);
    }

    /**
     * Cleanup memory
     */
    private function cleanupMemory(): void
    {
        gc_collect_cycles();
    }
}
