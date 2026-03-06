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

/**
 * Command to send arbitrary number of emails to Klaviyo
 */
class SendArbitaryOnlyKlaviyo extends Command
{
    use CommonFunctionsTrait, KlaviyoFunctionsTrait, SendSmsToQueueTrait;

    /** @var string Command signature */
    protected $signature = 'sendarbitary:onlyklaviyo {min} {max}';

    /** @var string Command description */
    protected $description = 'send a arbitrary number of email to klaviyo';

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

            $this->processContacts();

            $this->info('Execution Done');
        } catch (Throwable $e) {
            Log::error('SendArbitaryKlaviyo command failed: ' . $e->getMessage());
            $this->info('Error Occurred');
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
     * Process contacts to send to Klaviyo
     */
    private function processContacts(): void
    {
        $contacts = $this->getContactsToProcess();

        if ($contacts->isEmpty()) {
            $this->error('No Entry');
            return;
        }

        $this->sendToKlaviyo($contacts);
    }

    /**
     * Get contacts that need to be processed
     */
    private function getContactsToProcess()
    {
        $min = $this->argument('min');
        $max = $this->argument('max');
        $limit = $this->generateSecureRandomNumber($min, $max);

        return Contact::select('id', 'c_email')
            ->whereNull('email_sent_to_klaviyo')
            ->whereNotNull('c_email')
            ->where('c_email', '<>', '')
            ->where('verified_status', 'like', 'Verified%')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Send contacts to Klaviyo
     */
    private function sendToKlaviyo($contacts): void
    {
        foreach ($contacts as $key => $contact) {
            $this->sendKalviyo($contact, $key);
        }
    }
}
