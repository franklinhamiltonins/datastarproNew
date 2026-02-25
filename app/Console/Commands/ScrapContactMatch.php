<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\ScrapContactApiPlatform;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = 25;
        // echo 'Call For Scrap Contact Api Platform From Command With Limit ' . $limit;
        $resp = ScrapContactApiPlatform::callForScrapContactApiPlatform($limit, '');
        Log::info('Cron executed at '.Carbon::now()."\n");
        // dd($resp);
    }
}
