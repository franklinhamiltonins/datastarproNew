<?php

namespace App\Console\Commands;

use App\Model\SmsProviderQueue;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command to send SMS from queue
 */
class SmsSendFromQueue extends Command
{
    /** @var string Command signature */
    protected $signature = 'command:sms-send-from-queue';

    /** @var string Command description */
    protected $description = 'SMS send from Queue to send from there one by one';

    // Processing constants
    private const STATUS_PENDING = 0;
    private const STATUS_SENT = 1;
    private const STATUS_FAILED = 2;
    private const BATCH_LIMIT = 35;

    /**
     * Execute the console command
     */
    public function handle()
    {
        try {
            $messages = $this->getPendingMessages();

            foreach ($messages as $sms) {
                $this->processMessage($sms);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred at line ' . $e->getLine() . ' : ' . $e->getMessage());
        }
    }

    /**
     * Get pending messages from queue
     */
    private function getPendingMessages()
    {
        return SmsProviderQueue::select(
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
            ->where('sms_provider_queue.sms_sent_flag', self::STATUS_PENDING)
            ->limit(self::BATCH_LIMIT)
            ->get();
    }

    /**
     * Process single message
     */
    private function processMessage($sms): void
    {
        if (!empty($sms->c_phone)) {
            $this->sendMessage($sms);
        } else {
            $this->handleFailedMessage($sms);
        }
    }

    /**
     * Send message
     */
    private function sendMessage($sms): void
    {
        $sms->update([
            'sms_sent_flag' => self::STATUS_SENT,
        ]);

        Log::success('Message send to ' . $sms->c_phone . ' on ' . Carbon::now() . ' text : ' . $sms->text);
    }

    /**
     * Handle failed message
     */
    private function handleFailedMessage($sms): void
    {
        $sms->update([
            'sms_sent_flag' => self::STATUS_FAILED,
        ]);

        Log::error('Mobile No is empty for contact id' . $sms->id);
    }
}
