<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Model\LeadsModel\Contact;
use DB;
use Throwable;
use App\Jobs\AddEmailToKlaviyo;
use App\Traits\CommonFunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Traits\SendSmsToQueueTrait;

class SendArbitaryOnlyKlaviyo extends Command
{
    use CommonFunctionsTrait,KlaviyoFunctionsTrait,SendSmsToQueueTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendarbitary:onlyklaviyo {min} {max}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send a arbitary number of email to  klaviyo';

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
        try {
            $check_time_validity = $this->promotional_msg_sending_check();

            if($check_time_validity){
                $min = $this->argument('min');
                $max = $this->argument('max');

                $max_enteries_loop = rand($min,$max);

                $total_entry = Contact::select('id', 'c_email')
                ->whereNull('email_sent_to_klaviyo')
                ->whereNotNull('c_email')
                ->where('c_email', '<>', '')
                ->where('verified_status', 'like', 'Verified%')
                ->orderBy('id')->limit($max_enteries_loop)->get();

                // echo "<pre>";print_r($total_entry);exit;

                foreach ($total_entry as $keyentry => $valuentry) {
                    $this->sendkalviyo($valuentry,$keyentry);
                }
                $this->info('Execution Done');
                return 0;
            }
            else{
                $this->error("cant run at this time - time restriction 09:00 to 19:00 est");
            }
        }
        catch (Throwable $e) {
            Log::error("SendArbitaryKlaviyo command failed: " . $e->getMessage());
            $this->info('Error  Occured');
            
            // Re-throw the exception to allow Laravel to handle retries
            // throw $e;
        } finally {
            // Ensure the connection is closed after job execution
            DB::disconnect();
        }
    }
}
