<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\SmsProviderQueue;
use App\Model\SmsProvider;
use Carbon\Carbon;

use Vonage\Client;

class SmsSendFromQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sms-send-from-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SMS send from Queue to send from there one by one, by using contact and smsprovider table';

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


    public function handle(){
        try{
            // $vonage_key = env('VONAGE_KEY');
            // $vonage_secret = env('VONAGE_SECRET');
            // $vonage_from = env('VONAGE_FROM');
            // // $vonage_from = '+18882024249';

            // $vonage_new_client = new Client(new \Vonage\Client\Credentials\Basic($vonage_key, $vonage_secret));

            // Getting contacts where sms_sent_flag = 0
            // $smsToSend = SmsProviderQueue::select('*')
            //                             ->where('sms_sent_flag', '=', 0)
            //                             ->orderBy('id', 'desc')
            //                             ->limit(10)->get();  // send ran() for limit here

            $smsToSend = SmsProviderQueue::select(
                                        'sms_provider_queue.*', 
                                        'smsprovider.text', 
                                        'contacts.c_phone', 
                                        'contacts.current_sent_smsprovider_id', 
                                        'contacts.first_sms_date_time', 
                                        'contacts.next_sms_date_time', 
                                        'contacts.skip_response_step'
                                    )
                            ->join('contacts', 'contacts.id', '=', 'sms_provider_queue.contact_id')
                            ->join('smsprovider', 'smsprovider.id', '=', 'sms_provider_queue.sms_provider_id')
                            ->where('sms_provider_queue.sms_sent_flag', '0')
                            // ->Where('contacts.next_sms_date_time', '<=', Carbon::now())
                            // ->inRandomOrder()
                            ->limit(35)
                            ->get();

            foreach($smsToSend as $sms){

                if(!empty($sms->c_phone)){
                    // sleep / delay here for few millisecond


                    // // contact phone
                    // $contactPhone = Contact::where('id', $sms->contact_id)->pluck('c_phone')->first();
                    $contactPhone = $sms->c_phone;
                    echo $contactPhone;
                    dd($sms);

                    
                    // // sending sms from vonage
                    // $vonageResponse = $vonage_new_client->message()->send([
                    //  'to' => '+1 ' . $contactPhone,
                    //  'from' => $vonage_from, 
                    //  'text' => $sms->text,
                    // ]);

                    Log::success('Message send to ' . $contactPhone . ' on ' .  Carbon::now(). ' text : ' .$sms->text);


                    // $message = $vonageResponse->current();

                    $update = $sms->update([
                        'sms_sent_flag' => 1
                    ]);

                    // if($update){

                    // }
                }
                else{
                    $sms->update([
                        'sms_sent_flag' => 2
                    ]);

                    Log::error('Mobile No is empty for contact id' . $sms->id );
                }

                
            }

        } catch (\Exception $e) {
            Log::error('Exception occurred at line ' . $e->getLine() . ' : ' . $e->getMessage());
        }
    }
    
    
}
