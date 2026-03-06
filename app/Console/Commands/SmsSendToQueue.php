<?php

namespace App\Console\Commands;

use App\Model\SmsProvider;
use App\Traits\SendSmsToQueueTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Command to send SMS to queue for processing
 */
class SmsSendToQueue extends Command
{
    use SendSmsToQueueTrait;

    /** @var string Command signature */
    protected $signature = 'command:sms-send-to-queue {looplimit}';

    /** @var string Command description */
    protected $description = 'SMS send to Queue to send from there one by one, by using contact and smsprovider table';

    // Processing constants
    private const CHUNK_SIZE = 50;
    private const TIME_RESTRICTION_START = 9;
    private const TIME_RESTRICTION_END = 21;

    /**
     * Execute the console command
     */
    public function handle()
    {
        try {
            if ($this->isWithinAllowedTime()) {
                $this->error($this->getTimeRestrictionMessage());
                return;
            }

            $maxLimit = $this->argument('looplimit');
            $entryMade = $this->processContacts($maxLimit);

            if ($entryMade === 0) {
                $this->error('No entry Found');
            }
        } catch (Throwable $e) {
            Log::error('SendArbitaryKlaviyo command failed: ' . $e->getMessage());
        } finally {
            DB::disconnect();
        }
    }

    /**
     * Check if within allowed time range
     */
    private function isWithinAllowedTime(): bool
    {
        return $this->promotionalMsgSendingCheck();
    }

    /**
     * Get time restriction error message
     */
    private function getTimeRestrictionMessage(): string
    {
        return "Can't run at this time - time restriction " . self::TIME_RESTRICTION_START . ":00 to " . self::TIME_RESTRICTION_END . ":00 EST";
    }

    /**
     * Process contacts and add to queue
     */
    private function processContacts(int $maxLimit): int
    {
        $entryMade = 0;
        $todayTimestamp = Carbon::now()->toDateTimeString();

        $query = $this->buildContactQuery($todayTimestamp);

        $query->chunk(self::CHUNK_SIZE, function ($contacts) use (&$entryMade, $maxLimit) {
            foreach ($contacts as $contact) {
                if ($this->shouldSkipContact($contact)) {
                    continue;
                }

                $processed = $this->processContact($contact);

                if ($processed) {
                    $entryMade++;
                }

                if ($entryMade >= $maxLimit) {
                    return false;
                }
            }
        });

        return $entryMade;
    }

    /**
     * Build the contact query
     */
    private function buildContactQuery(string $todayTimestamp)
    {
        return DB::table('contacts')
            ->join('leads', 'contacts.lead_id', '=', 'leads.id')
            ->leftJoin(
                DB::raw("(SELECT DISTINCT lead_id FROM dialings_leads WHERE status = 'own') AS removalskip"),
                'contacts.lead_id',
                '=',
                'removalskip.lead_id'
            )
            ->whereNull('contacts.deleted_at')
            ->whereNotNull('contacts.c_phone')
            ->where('contacts.c_phone', '!=', '')
            ->where('contacts.respond_to_cron_flag', 0)
            ->where('contacts.has_initiated_stop_chat', 0)
            ->whereNull('removalskip.lead_id')
            ->where('leads.is_client', 0)
            ->where('verified_status', 'like', 'Verified%')
            ->where(function ($query) use ($todayTimestamp) {
                $query->whereNull('contacts.next_sms_date_time')
                    ->orWhere('contacts.next_sms_date_time', '<=', $todayTimestamp);
            })
            ->orderBy('contacts.skip_response_step', 'DESC')
            ->orderBy('contacts.next_sms_date_time')
            ->select('contacts.*');
    }

    /**
     * Check if contact should be skipped
     */
    private function shouldSkipContact($contact): bool
    {
        if (empty($contact->skip_response_step)) {
            return false;
        }

        return $this->checkContactExistsInQueue($contact);
    }

    /**
     * Check if contact exists in SMS queue recently
     */
    private function checkContactExistsInQueue($contact): bool
    {
        $dayGaps = $this->getDayGaps($contact);

        if (empty($dayGaps)) {
            return false;
        }

        $gap = $this->calculateGap($dayGaps);

        return DB::table('sms_provider_queue')
            ->where('contact_id', $contact->id)
            ->where('created_at', '>=', Carbon::now()->subDays($gap))
            ->exists();
    }

    /**
     * Get day gaps from SMS provider
     */
    private function getDayGaps($contact)
    {
        return SmsProvider::select('day_delay')
            ->orderBy('day_delay', 'asc')
            ->orderBy('minute_delay', 'asc')
            ->skip(max(0, $contact->skip_response_step - 1))
            ->limit(2)
            ->pluck('day_delay');
    }

    /**
     * Calculate gap between SMS sends
     */
    private function calculateGap($dayGaps): int
    {
        if ($dayGaps->count() > 1) {
            return $dayGaps[1] - $dayGaps[0];
        }

        return (int) $dayGaps->first() ?? 0;
    }

    /**
     * Process a single contact
     */
    private function processContact($contact): bool
    {
        $isFirstTime = is_null($contact->current_sent_smsprovider_id);
        $smsProvider = $this->getSmsProvider($contact, $isFirstTime);

        if (empty($smsProvider)) {
            return false;
        }

        $this->updateContactTableForNextSmsProvider(
            $smsProvider->id,
            $contact,
            $isFirstTime,
            1,
            $smsProvider->day_delay
        );

        return true;
    }
}
