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


class ScrapYellowPages extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:scrap-yellow-pages';

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
		$total = 0;
		$each = 0;
		$paginate_total = 0;
		$paginate = 1;
		$arr = [];
		$data = [];
		$crawler_str = '';
		$updated_status = 3; // failed

		// $searchColl = DB::table('scrap_cities')->where('status', 1)->limit(2)->get();
		$searchColl = ScrapCity::with('scrapCounty')->where('status', 1)->limit(2)->get();

		Log::channel('yellowpagesscrap')->info('Cron started at ' . Carbon::now());

		foreach ($searchColl as $search) :

			do {
				$crawler_str = 'https://www.yellowpages.com/search?search_terms=' . $search->search_keyword . '&geo_location_terms=' . $search->city . '%2C%20' . $search->state_code;

				if ($paginate > 1)
					$crawler_str .= '&page=' . $paginate;
				$response = Http::get($crawler_str);
				array_push($arr, $crawler_str);
				// Check if request was successful
				if ($response->successful()) {
					// Create a new Crawler instance
					$crawler = new Crawler($response->body());

					if ($paginate_total == 0) {
						$paginationStr = $crawler->filter('[class^="showing-count"]')->text();
						$pieces = explode(" ", $paginationStr);

						$total = (int) $pieces[3]; //30
						$each = (int) explode('-', $pieces[1])[1];

						$paginate_total = (int)ceil($total / $each);
					}


					$crawler->filter('[class^="result"][id^="lid-"]')->each(function ($node) use (&$data, &$search) {
						$businessName = $node->filter('h2.n')->text();
						$businessName = preg_replace('/^\d+\.\s*/', '', $businessName);
						$phone = $node->filter('.phones.phone.primary')->count() ? $node->filter('.phones.phone.primary')->text() : '';
						// echo $node->filter('.street-address')->count() > 0 ? $node->filter('.street-address')->text() : '';
						$streetAddress = $node->filter('.street-address')->count() > 0 ? $node->filter('.street-address')->text() : '';
						$locality = $node->filter('.locality')->count() > 0 ? $node->filter('.locality')->text() : '';

						preg_match('/([^,]+),\s*(\w+)\s+(\d+)/', $locality, $matches);
						$city = $matches[1] ?? '';
						$state = $matches[2] ?? '';
						$zipCode = $matches[3] ?? '';

						// Store name and h2 text in the data array
						$data[] = [
							'businessName' => $businessName,
							'phone' => $phone,
							'streetAddress' => $streetAddress,
							'city' => $city,
							'zipCode' => $zipCode,
							'state' => $state,
							'locality' => $locality,
							'county' => $search->scrapCounty->name,
							'county_id' => $search->county_id,
						];
					});
					$paginate++;
				} else {
					// Handle failed request
					Log::error('Failed to fetch website data' . $crawler_str);
				}
			} while ($paginate <= $paginate_total);

			if (count($data) >= 0) :
				$updated_status = 2;
				$scrapStatus = Lead::evaluateCrawlerLeads($data, $search->search_keyword);
				Log::channel('yellowpagesscrap')->info('Scrap status of ' . $crawler_str . ' is ' . json_encode($scrapStatus));

			endif;

			// update status of the record
			DB::table('scrap_cities')
				->where('id', $search->id)
				->update(['status' => $updated_status]);


		endforeach;


		Log::channel('yellowpagesscrap')->info('Cron stopped at ' . Carbon::now() . "\n");
	}
}
