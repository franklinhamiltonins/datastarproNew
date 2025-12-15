<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Model\LeadsModel\Contact;
use App\Model\SmsProviderQueue;
use App\Model\SmsProvider;
use Carbon\Carbon;
use DB;
use Throwable;
use App\Traits\SendSmsToQueueTrait;

class SmsSendToQueue extends Command
{
    use SendSmsToQueueTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sms-send-to-queue {looplimit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SMS send to Queue to send from there one by one, by using contact and smsprovider table';

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

            if (!$check_time_validity) {
                $max_limit = $this->argument('looplimit'); 
                $entry_made = 0;  // Track processed entries
                $chunkSize = 50;   // Process contacts in chunks of 50
                $todaytimestamp = Carbon::now()->toDateTimeString();

                DB::table('contacts')
                    ->join('leads', 'contacts.lead_id', '=', 'leads.id')
                    ->leftJoin(DB::raw("(SELECT DISTINCT lead_id FROM dialings_leads WHERE status = 'own') AS removalskip"), 'contacts.lead_id', '=', 'removalskip.lead_id')
                    ->whereNull('contacts.deleted_at')
                    ->whereNotNull('contacts.c_phone')
                    ->where('contacts.c_phone', '!=', '')
                    ->where('contacts.respond_to_cron_flag', 0)
                    ->where('contacts.has_initiated_stop_chat', 0)
                    ->whereNull('removalskip.lead_id')
                    ->where('leads.is_client', 0)
                    ->where('verified_status', 'like', 'Verified%')
                    ->where(function ($query) use ($todaytimestamp) {
                        $query->whereNull('contacts.next_sms_date_time')
                              ->orWhere('contacts.next_sms_date_time', '<=', $todaytimestamp);
                    })
                    ->orderBy('contacts.skip_response_step', 'DESC')
                    ->orderBy('contacts.next_sms_date_time')
                    ->select('contacts.*')
                    ->chunk($chunkSize, function ($contacts) use (&$entry_made, $max_limit) {
                        foreach ($contacts as $contact) {
                            // echo "<pre>";print_r($contacts);exit;
                            // **Check if contact exists in sms_provider_queue in last 30 days**

                            $check_entry = !empty($contact->skip_response_step)? true : false;

                            $continue_check = false;

                            if($check_entry){
                                $days_gaps = SmsProvider::select('day_delay')
                                ->orderBy('day_delay', 'asc')
                                ->orderBy('minute_delay', 'asc')
                                ->skip(max(0, $contact->skip_response_step - 1)) // Prevent negative skip
                                ->limit(2) // Fetch both current and previous step delays
                                ->pluck('day_delay'); // Get only the 'day_delay' column

                                // Extract values with fallback
                                $days_gap_old = $days_gaps->count() > 1 ? $days_gaps[0] : null;
                                $days_gap = $days_gaps->count() > 1 ? $days_gaps[1] : ($days_gaps->first() ?? null);

                                // Calculate gap safely
                                $gap = $days_gap_old !== null ? $days_gap - $days_gap_old : 0;

                                $exists = DB::table('sms_provider_queue')
                                ->where('contact_id', $contact->id)
                                ->where('created_at', '>=', Carbon::now()->subDays($gap))
                                ->exists();

                                if($exists){
                                    $continue_check = true;
                                }
                                
                            }

                            if ($continue_check) {
                                continue;  // **Skip this contact and go to next**
                            }

                            // **Process the contact**
                            $isFirstTime = is_null($contact->current_sent_smsprovider_id);
                            $smsProvider = $this->getSmsProvider($contact, $isFirstTime);

                            if (empty($smsProvider)) {
                                continue;
                            }

                            if ($smsProvider) {
                                $this->updatecontacttable_fornextsmsprovider($smsProvider->id, $contact, $isFirstTime, 1, $smsProvider->day_delay);
                            }

                            // **Increase processed count**
                            $entry_made++;

                            // **Break if entry limit is reached**
                            if ($entry_made >= $max_limit) {
                                return false; // **Stop chunk processing**
                            }
                        }
                    });

                if ($entry_made === 0) {
                    $this->error("No entry Found");
                }
            } else {
                $this->error("Can't run at this time - time restriction 09:00 to 21:00 EST");
            }
        } catch (Throwable $e) {
            Log::error("SendArbitaryKlaviyo command failed: " . $e->getMessage());
        } finally {
            DB::disconnect(); // Ensure database connection is closed
        }
    }

    public function handle_24feb()
    {
        try {
            $check_time_validity = $this->promotional_msg_sending_check();

            if($check_time_validity){
                $max_limit = $this->argument('looplimit');
                // $min_value = floor($max_limit * 0.75); 

                // $totalLimit = rand($min_value,$max_limit);
                $todaytimestamp = Carbon::now()->toDateTimeString();

                $sql_query = "SELECT contacts.* FROM contacts INNER JOIN leads ON contacts.lead_id = leads.id Left join (SELECT DISTINCT lead_id FROM dialings_leads where status = 'own') as removalskip ON contacts.lead_id = removalskip.lead_id where contacts.deleted_at IS NULL AND contacts.c_phone is not null and contacts.c_phone != '' and contacts.respond_to_cron_flag = 0 and contacts.has_initiated_stop_chat = 0 and removalskip.lead_id IS NULL and leads.is_client = 0 and verified_status like 'Verified%' and  (contacts.next_sms_date_time IS NULL OR next_sms_date_time <= '".$todaytimestamp."') order BY contacts.skip_response_step DESC , contacts.next_sms_date_time";

                $sql_query .= " limit ".$max_limit;

                // echo "<pre>";print_r($sql_query);exit;

                $contactobj = DB::select($sql_query);

                if(count($contactobj) > 0){
                    foreach($contactobj as $contact){
                        $isFirstTime = is_null($contact->current_sent_smsprovider_id);

                        // echo $isFirstTime ? "   if run --- <br>".$contact->id : "   if ELSE run --- <br>".$contact->id;

                        $smsProvider = $this->getSmsProvider($contact, $isFirstTime);
                        if(empty($smsProvider)){
                            continue;
                        }
                    
                        if ($smsProvider) {
                            $this->updatecontacttable_fornextsmsprovider($smsProvider->id,$contact,$isFirstTime,1,$smsProvider->day_delay);
                        }
                    }
                }
                else{
                    $this->error("No entry Found");
                }
            }
            else{
                $this->error("cant run at this time - time restriction 09:00 to 21:00 est");
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