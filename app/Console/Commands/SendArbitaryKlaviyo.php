<?php

namespace App\Console\Commands;

use App\Model\SmsProviderQueue;
use App\Traits\CommonFunctionsTrait;
use App\Traits\KlaviyoFunctionsTrait;
use App\Traits\SendSmsToQueueTrait;
use App\Traits\VontageunctionsTrait;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Command to send arbitrary contact info to Klaviyo
 */
class SendArbitaryKlaviyo extends Command
{
    use CommonFunctionsTrait, KlaviyoFunctionsTrait, SendSmsToQueueTrait, VontageunctionsTrait;

    /** @var string Command signature */
    protected $signature = 'sendarbitary:klaviyo {min} {max}';

    /** @var string Command description */
    protected $description = 'arbitary send contact info to klaviyo';

    // Time restriction constants
    private const MIN_HOUR = 9;
    private const MAX_HOUR = 19;

    /**
     * Execute the console command
     */
    public function handle()
    {
        try {
            if (!$this->isWithinAllowedTime()) {
                $this->error('cant run at this time - time restriction 09:00 to 19:00 est');
                return 0;
            }

            $this->processQueueEntries();

            $this->info('Execution Done');
        } catch (Throwable $e) {
            Log::error('SendArbitaryKlaviyo command failed: ' . $e->getMessage());
        } finally {
            DB::disconnect();
        }

        return 0;
    }

    /**
     * Check if current time is within allowed sending time
     */
    private function isWithinAllowedTime(): bool
    {
        return $this->promotionalMsgSendingCheck();
    }

    /**
     * Process queue entries
     */
    private function processQueueEntries(): void
    {
        $entries = $this->getQueueEntries();

        if ($entries->isEmpty()) {
            $this->error('No Entry');
            return;
        }

        $this->sendEntries($entries);
    }

    /**
     * Get pending queue entries
     */
    private function getQueueEntries()
    {
        $min = $this->argument('min');
        $max = $this->argument('max');
        $limit = $this->generateSecureRandomNumber($min, $max);

        return SmsProviderQueue::where('sms_sent_flag', 0)
            ->select('id', 'contact_id', 'sms_sent_flag', 'sms_provider_id', 'day_delay')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Send entries to Vonage
     */
    private function sendEntries($entries): void
    {
        foreach ($entries as $key => $entry) {
            $this->sendVonageSmsFromQueue($entry, $key);
        }
    }
}
