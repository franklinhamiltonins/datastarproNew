<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Lead;
use DB;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Model\ScrapCity;

use Spatie\Browsershot\Browsershot;

use App\Services\GetLangLongGoogleService;


class ScrapYellowPagesNew extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:scrap-yellow-pages-new';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'It will scrap the businesses from yellow pages and push in the database';

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
	    Log::channel('yellowpagesscrap')->info('Cron started at ' . Carbon::now());

	    // Process ScrapCity records in chunks of 2
	    ScrapCity::with('scrapCounty')
        ->where('status', 1)
        ->chunk(5, function ($searchColl){

            foreach ($searchColl as $search) {

                $total = 0;
                $each = 0;
                $paginate_total = 0;
                $paginate = 1;
                $data = [];
                $updated_status = 3; // default = failed

                do {

                    // Build URL
                    $crawler_str = 'https://www.yellowpages.com/search?search_terms='
                        . $search->search_keyword . '&geo_location_terms='
                        . $search->city . '%2C%20' . $search->state_code;

                    if ($paginate > 1) {
                        $crawler_str .= '&page=' . $paginate;
                    }

                    try {

                        $html = Browsershot::url($crawler_str)
                            ->timeout(60)
                            ->waitUntilNetworkIdle()
                            ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
                            ->bodyHtml();

                    } catch (\Exception $e) {

                        Log::error("Browsershot failed for URL: " . $crawler_str . " | " . $e->getMessage());
                        break;
                    }

                    $crawler = new \Symfony\Component\DomCrawler\Crawler($html);

                    // Detect pagination only once
                    if ($paginate_total == 0) {

                        try {
                            $paginationStr = $crawler->filter('[class^="showing-count"]')->text();
                            $pieces = explode(" ", $paginationStr);

                            // Example: “Showing 1-30 of 450”
                            $total = (int)$pieces[3];
                            $each = (int)explode('-', $pieces[1])[1];
                            $paginate_total = ceil($total / $each);

                        } catch (\Exception $e) {
                            $paginate_total = 1;
                        }
                    }

                    // Extract business results
                    $crawler->filter('[class^="result"][id^="lid-"]')->each(function ($node) use (&$data, &$search) {

                        $businessName = $node->filter('h2.n')->count()
                            ? $node->filter('h2.n')->text()
                            : '';

                        $businessName = preg_replace('/^\d+\.\s*/', '', $businessName);

                        $phone = $node->filter('.phones.phone.primary')->count()
                            ? $node->filter('.phones.phone.primary')->text()
                            : '';

                        $streetAddress = $node->filter('.street-address')->count()
                            ? $node->filter('.street-address')->text()
                            : '';

                        $locality = $node->filter('.locality')->count()
                            ? $node->filter('.locality')->text()
                            : '';

                        preg_match('/([^,]+),\s*(\w+)\s+(\d+)/', $locality, $matches);
                        $city = $matches[1] ?? '';
                        $state = $matches[2] ?? '';
                        $zipCode = $matches[3] ?? '';


						$latitude = NUll;
						$longitude = NUll;
						if ($streetAddress) {
							$new_address = $streetAddress;
							$new_address = $city ? $new_address . ' ' . $city . ',' :  $new_address;
							$new_address = $state ? $new_address . ' ' . $state . ',' :  $new_address;
							$new_address = $zipCode ? $new_address . ' ' . $zipCode . ',' :  $new_address;
							$googleService = new GetLangLongGoogleService();
							$lat_long = $googleService->getLatLngFromGoogleService($new_address);
							if (!is_null($lat_long['lat']) && !is_null($lat_long['long'])) {
								$latitude = $lat_long['lat'];
								$longitude = $lat_long['long'];
							}
						}

                        $data[] = [
                            'type' => $search->search_keyword,
                            'businessName' => $businessName,
                            'phone'        => $phone,
                            'address1'=> $streetAddress,
                            'address2'=> "",
                            'city'         => $city,
                            'zip'      => $zipCode,
                            'state'        => $state,
                            'locality'     => $locality,
                            'latitude'     => $latitude,
                            'longitude'     => $longitude,
                            'county'       => $search->scrapCounty->name,
                            'county_id'    => $search->county_id,
                        ];
                    });

                    $paginate++;

                } while ($paginate <= $paginate_total);

                // After scraping finished
                if (count($data) > 0) {
                    $updated_status = 2; // success

                    $lead = new Lead();
                    $scrapStatus = $lead->evaluateCrawlerLeads($data, $search->search_keyword);

                    Log::channel('yellowpagesscrap')->info(
                        'Scrap status of ' . json_encode($search->toArray()) . ' / URL: ' . $crawler_str
                    );
                }

                unset($crawler);
				unset($html);
				unset($data);
				gc_collect_cycles();


                // Update status
                DB::table('scrap_cities')
                    ->where('id', $search->id)
                    ->update(['status' => $updated_status]);
            }
            // exit;
        });

	    Log::channel('yellowpagesscrap')->info('Cron stopped at ' . Carbon::now() . "\n");
	}


}
