<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\ScrapContactApiPlatform;
use DB;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Model\ScrapCity;


class ScrapContactMatch extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:scrap-contact-match';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'It will fetch records from thridparty apis and update in contacts';

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
		$limit = 25;
		// echo 'Call For Scrap Contact Api Platform From Command With Limit ' . $limit;
		$resp = ScrapContactApiPlatform::callForScrapContactApiPlatform($limit, '');
		Log::info('Cron executed at ' . Carbon::now() . "\n");
		// dd($resp);
	}
}
