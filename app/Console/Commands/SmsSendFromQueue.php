<?php

namespace App\Console\Commands;

use App\Model\SmsProviderQueue;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
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

            foreach ($smsToSend as $sms) {

                if (! empty($sms->c_phone)) {
                    $contactPhone = $sms->c_phone;

                    Log::success('Message send to '.$contactPhone.' on '.Carbon::now().' text : '.$sms->text);

                    $update = $sms->update([
                        'sms_sent_flag' => 1,
                    ]);
                } else {
                    $sms->update([
                        'sms_sent_flag' => 2,
                    ]);

                    Log::error('Mobile No is empty for contact id'.$sms->id);
                }
            }

        } catch (\Exception $e) {
            Log::error('Exception occurred at line '.$e->getLine().' : '.$e->getMessage());
        }
    }
}
