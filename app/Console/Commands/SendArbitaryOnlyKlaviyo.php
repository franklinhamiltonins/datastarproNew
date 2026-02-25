<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use App\Traits\CommonFunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Traits\SendSmsToQueueTrait;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $check_time_validity = $this->promotionalMsgSendingCheck();

            if ($check_time_validity) {
                $min = $this->argument('min');
                $max = $this->argument('max');

                $max_enteries_loop = $this->generateSecureRandomNumber($min, $max);

                $total_entry = Contact::select('id', 'c_email')
                    ->whereNull('email_sent_to_klaviyo')
                    ->whereNotNull('c_email')
                    ->where('c_email', '<>', '')
                    ->where('verified_status', 'like', 'Verified%')
                    ->orderBy('id')->limit($max_enteries_loop)->get();

                // echo "<pre>";print_r($total_entry);exit;

                foreach ($total_entry as $keyentry => $valuentry) {
                    $this->sendKalviyo($valuentry, $keyentry);
                }
                $this->info('Execution Done');

                return 0;
            } else {
                $this->error('cant run at this time - time restriction 09:00 to 19:00 est');
            }
        } catch (Throwable $e) {
            Log::error('SendArbitaryKlaviyo command failed: '.$e->getMessage());
            $this->info('Error  Occured');

            // Re-throw the exception to allow Laravel to handle retries
            // throw $e;
        } finally {
            // Ensure the connection is closed after job execution
            DB::disconnect();
        }
    }
}
