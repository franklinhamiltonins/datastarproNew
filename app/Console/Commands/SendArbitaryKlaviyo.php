<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Model\LeadsModel\Contact;
use App\Model\SmsProviderQueue;
use App\Model\SmsProvider;
use DB;
use Throwable;
use App\Jobs\AddEmailToKlaviyo;
use App\Jobs\SendSmsVontageThroughQueue;
use App\Traits\CommonFunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Traits\VontageunctionsTrait;
use App\Traits\SendSmsToQueueTrait;

class SendArbitaryKlaviyo extends Command
{
    use CommonFunctionsTrait,KlaviyoFunctionsTrait,VontageunctionsTrait,SendSmsToQueueTrait;
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

                $count_of_enteries_query = SmsProviderQueue::
                where('sms_sent_flag',0);


                $count_of_enteries = $count_of_enteries_query->count();
                // $count_of_enteries = 0;

                if($count_of_enteries == 0){
                    $this->error('No Entry');
                }
                else{
                    // $count_of_enteries = 25000;

                    // $min_smsprovider_id = SmsProvider::orderBy('day_delay', 'ASC')->orderBy('minute_delay', 'ASC')->value('id');

                    $loop_pick_entry = $max_enteries_loop;
                    // $loop_pick_entry = $this->fetcharbitarycount($count_of_enteries,$max_enteries_loop);


                    $count_of_enteries_obj = $count_of_enteries_query->select('id','contact_id','sms_sent_flag','sms_provider_id','day_delay')
                    ->orderBy('id')->limit($loop_pick_entry)->get();

                    foreach ($count_of_enteries_obj as $keyentry => $valuentry) {
                        // if($valuentry->klaviyo_sent_flag == 0 && $valuentry->sms_provider_id == $min_smsprovider_id){
                        //     $this->sendkalviyo($valuentry,$keyentry);
                        // }else{
                        //     $this->updateklaviyostatus_insmsproviderqueue($valuentry->id,4);
                        // }
                        
                        $this->sendvonagesms_fromqueue($valuentry,$keyentry);
                    }

                    $this->info('Execution Done');
                }
                return 0;
            }
            else{
                $this->error("cant run at this time - time restriction 09:00 to 19:00 est");
            }
        }
        catch (Throwable $e) {
            Log::error("SendArbitaryKlaviyo command failed: " . $e->getMessage());
            
            // Re-throw the exception to allow Laravel to handle retries
            // throw $e;
        } finally {
            // Ensure the connection is closed after job execution
            DB::disconnect();
        }
    }
}
