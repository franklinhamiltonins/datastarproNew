<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use App\Model\ScrapCity;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Command to scrape businesses from Yellow Pages
 */
class ScrapYellowPages extends Command
{
    /** @var string Command signature */
    protected $signature = 'command:scrap-yellow-pages';

    /** @var string Command description */
    protected $description = 'It will scrap the businesses from yellow pages and push in the database';

    // Base URL for Yellow Pages search
    private const BASE_URL = 'https://www.yellowpages.com/search';

    // Processing constants
    private const STATUS_PENDING = 1;
    private const STATUS_SUCCESS = 2;
    private const STATUS_FAILED = 3;
    private const DEFAULT_LIMIT = 2;

    /**
     * Execute the console command
     */
    public function handle()
    {
        Log::channel('yellowpagesscrap')->info('Cron started at ' . Carbon::now());

        $searchCollection = $this->getSearchCollection();

        foreach ($searchCollection as $search) {
            $this->processSearch($search);
        }

        Log::channel('yellowpagesscrap')->info('Cron stopped at ' . Carbon::now() . "\n");
    }

    /**
     * Get search collection to process
     */
    private function getSearchCollection()
    {
        return ScrapCity::with('scrapCounty')
            ->where('status', self::STATUS_PENDING)
            ->limit(self::DEFAULT_LIMIT)
            ->get();
    }

    /**
     * Process a single search
     */
    private function processSearch($search)
    {
        $businessData = $this->scrapeBusinessData($search);
        $this->saveBusinessData($businessData, $search);
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
            $response = Http::get($url);

            if ($this->isSuccessfulResponse($response)) {
                $crawler = new Crawler($response->body());
                $paginateTotal = $this->calculatePaginationTotal($crawler, $paginateTotal);
                $data = array_merge($data, $this->extractBusinessData($crawler, $search));
            } else {
                Log::error('Failed to fetch website data' . $url);
            }

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
     * Check if response is successful
     */
    private function isSuccessfulResponse($response): bool
    {
        return $response->successful();
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

            return (int) ceil($total / $each);
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
        return [
            'businessName' => $this->getBusinessName($node),
            'phone' => $this->getPhone($node),
            'streetAddress' => $this->getStreetAddress($node),
            'city' => $this->getCity($node),
            'zipCode' => $this->getZipCode($node),
            'state' => $this->getState($node),
            'locality' => $this->getLocality($node),
            'county' => $search->scrapCounty->name ?? '',
            'county_id' => $search->county_id,
        ];
    }

    /**
     * Get business name from node
     */
    private function getBusinessName($node): string
    {
        $name = $node->filter('h2.n')->text();
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
        return $node->filter('.street-address')->count() > 0
            ? $node->filter('.street-address')->text()
            : '';
    }

    /**
     * Get locality from node
     */
    private function getLocality($node): string
    {
        return $node->filter('.locality')->count() > 0
            ? $node->filter('.locality')->text()
            : '';
    }

    /**
     * Get city from locality
     */
    private function getCity($node): string
    {
        return $this->parseLocality($node)[0] ?? '';
    }

    /**
     * Get state from locality
     */
    private function getState($node): string
    {
        return $this->parseLocality($node)[1] ?? '';
    }

    /**
     * Get zip code from locality
     */
    private function getZipCode($node): string
    {
        return $this->parseLocality($node)[2] ?? '';
    }

    /**
     * Parse locality into city, state, zip
     */
    private function parseLocality($node): array
    {
        $locality = $this->getLocality($node);
        preg_match('/([^,]+),\s*(\w+)\s+(\d+)/', $locality, $matches);

        return [
            $matches[1] ?? '',
            $matches[2] ?? '',
            $matches[3] ?? '',
        ];
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
                'Scrap status is ' . json_encode($scrapStatus)
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
}
