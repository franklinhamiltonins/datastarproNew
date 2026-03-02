<?php

namespace App\Console\Commands;

use App\Model\SmsProvider;
use App\Model\SmsProviderQueue;
use App\Traits\CommonFunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Traits\SendSmsToQueueTrait;
use App\Traits\VontageunctionsTrait;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendArbitaryKlaviyo extends Command
{
    use CommonFunctionsTrait,KlaviyoFunctionsTrait,SendSmsToQueueTrait,VontageunctionsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendarbitary:klaviyo {min} {max}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'arbitary send contact info to klaviyo';

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

                $count_of_enteries_query = SmsProviderQueue::where('sms_sent_flag', 0);

                $count_of_enteries = $count_of_enteries_query->count();

                if ($count_of_enteries == 0) {
                    $this->error('No Entry');
                } else {

                    $loop_pick_entry = $max_enteries_loop;

                    $count_of_enteries_obj = $count_of_enteries_query->select('id', 'contact_id', 'sms_sent_flag', 'sms_provider_id', 'day_delay')
                        ->orderBy('id')->limit($loop_pick_entry)->get();

                    foreach ($count_of_enteries_obj as $keyentry => $valuentry) {
                        $this->sendVonageSmsFromQueue($valuentry, $keyentry);
                    }

                    $this->info('Execution Done');
                }

                return 0;
            } else {
                $this->error('cant run at this time - time restriction 09:00 to 19:00 est');
            }
        } catch (Throwable $e) {
            Log::error('SendArbitaryKlaviyo command failed: '.$e->getMessage());
        } finally {
            // Ensure the connection is closed after job execution
            DB::disconnect();
        }
    }
}
