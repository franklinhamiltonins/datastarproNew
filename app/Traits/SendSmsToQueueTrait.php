<?php

namespace App\Traits;

use App\Jobs\AddEmailToKlaviyo;
use App\Jobs\SendSmsVontageThroughQueue;
use App\Model\LeadsModel\Contact;
use App\Model\SmsProvider;
use App\Model\SmsProviderQueue;
use Carbon\Carbon;

// before using this trait , VontageunctionsTrait trait also need to use
trait SendSmsToQueueTrait
{
    /**
     * Get next SMS provider based on skip count
     */
    protected function getNextSmsProvider($contact, int $skip)
    {
        return SmsProvider::select('*')
            ->orderBy('day_delay', 'asc')
            ->orderBy('minute_delay', 'asc')
            ->offset($skip)
            ->limit(1)
            ->first();
    }

    public function nextSmsProvider($contact, $isFirstTime)
    {
        if ($isFirstTime) {
            $skip = 1;
        } else {
            $skip = ($contact->skip_response_step + 1);
        }

        return $this->getNextSmsProvider($contact, $skip);
    }

    /**
     * Get the appropriate SMS Provider based on the contact state.
     */
    public function getSmsProvider($contact, $isFirstTime)
    {
        $skip = $isFirstTime ? 0 : $contact->skip_response_step;

        return $this->getNextSmsProvider($contact, $skip);
    }

    /**
     * Calculate the delay date time based on SMS Provider settings.
     */
    public function calculateDelayDateTime($startDate, $smsProvider)
    {
        return $smsProvider->minute_delay == 0
            ? $startDate->addDays($smsProvider->day_delay)
            : $startDate->addDays($smsProvider->day_delay)->addMinutes($smsProvider->minute_delay);
    }

    /**
     * Get minimum SMS provider ID
     */
    protected function getMinSmsProviderId(): int
    {
        return SmsProvider::orderBy('day_delay', 'ASC')
            ->orderBy('minute_delay', 'ASC')
            ->value('id') ?? 0;
    }

    /**
     * Send Klaviyo email for contact
     */
    protected function sendKlaviyoEmail($contact): void
    {
        if (! empty($contact->c_email)) {
            $this->sendemailtoklaviyoinside($contact);
        }
    }

    /**
     * Send Vontage message for contact
     */
    protected function sendVontageMessage($minSmsproviderId, $contact): void
    {
        if (! empty($contact->c_phone)) {
            $this->sendvontagemessageinsidefirst($minSmsproviderId, $contact);
        }
    }

    public function sendContactWiseSmsProviderandKlaviyo($contact, $isFirstTime = true)
    {
        if (! empty($contact->c_email) || ! empty($contact->c_phone)) {
            $minSmsproviderId = $this->getMinSmsProviderId();

            $this->updateContactTableForNextSmsProvider($minSmsproviderId, $contact, $isFirstTime, 0, 0);

            // addition to klaviyo
            $this->sendKlaviyoEmail($contact);
            // send first vontage message
            $this->sendVontageMessage($minSmsproviderId, $contact);
        }
    }

    /**
     * Calculate delay datetime for next SMS
     */
    protected function calculateNextDelayDateTime($contact, $isFirstTime, $nextSmsProvider): array
    {
        $startDate = $isFirstTime ? Carbon::now() : Carbon::parse($contact->first_sms_date_time);

        if ($nextSmsProvider) {
            $delayDateTime = $this->calculateDelayDateTime($startDate, $nextSmsProvider);
            $respondToCronFlag = 0;
        } else {
            $delayDateTime = $contact->next_sms_date_time;
            $respondToCronFlag = 1;
        }

        return [$delayDateTime, $respondToCronFlag];
    }

    public function updateContactTableForNextSmsProvider($currentSentSmsProviderId, $contact, $isFirstTime, $makeloginqueue, $dayDelay)
    {
        $nextSmsProvider = $this->nextSmsProvider($contact, $isFirstTime);
        [$delayDateTime, $respondToCronFlag] = $this->calculateNextDelayDateTime(
            $contact,
            $isFirstTime,
            $nextSmsProvider
        );

        Contact::where('id', $contact->id)
            ->update([
                'current_sent_smsprovider_id' => $currentSentSmsProviderId,
                'first_sms_date_time' => $isFirstTime ? Carbon::now() : $contact->first_sms_date_time,
                'next_sms_date_time' => $delayDateTime,
                'respond_to_cron_flag' => $respondToCronFlag,
                'skip_response_step' => $isFirstTime ? 1 : $contact->skip_response_step + 1,
            ]);

        if ($makeloginqueue == 1) {
            SmsProviderQueue::create([
                'contact_id' => $contact->id,
                'sms_sent_flag' => 0,
                'sms_provider_id' => $currentSentSmsProviderId,
                'day_delay' => $dayDelay,
            ]);
        }
    }

    public function sendemailtoklaviyoinside($contact)
    {
        AddEmailToKlaviyo::dispatch($contact);
        $this->updateKlaviyoStatusIncontactable($contact->id, 1);
    }

    public function sendvontagemessageinsidefirst($minSmsproviderId, $contact)
    {
        $requestData = $this->VontageQueueRequestData($minSmsproviderId, $contact->c_phone, $contact->id);

        SendSmsVontageThroughQueue::dispatch($requestData);
    }

    /**
     * Check if new contact info needs SMS provider update
     */
    protected function needsSmsProviderUpdate($oldcontact, $contact): bool
    {
        return empty($oldcontact->c_email) && empty($oldcontact->c_phone) &&
            (! empty($contact->c_email) || ! empty($contact->c_phone));
    }

    /**
     * Handle Klaviyo email for updated contact
     */
    protected function handleUpdatedContactKlaviyo($oldcontact, $contact): void
    {
        if (empty($oldcontact->c_email) && ! empty($contact->c_email)) {
            $this->sendemailtoklaviyoinside($contact);
        }
    }

    /**
     * Handle Vontage message for updated contact
     */
    protected function handleUpdatedContactVontage($oldcontact, $contact, int $minSmsproviderId): void
    {
        if (empty($oldcontact->c_phone) && ! empty($contact->c_phone)) {
            $this->sendvontagemessageinsidefirst($minSmsproviderId, $contact);
        }
    }

    public function updateContactInfomationBasedKlaviyoVontage($oldcontact, $contact, $isFirstTime = true)
    {
        $minSmsproviderId = $this->getMinSmsProviderId();

        if ($this->needsSmsProviderUpdate($oldcontact, $contact)) {
            $this->updateContactTableForNextSmsProvider($minSmsproviderId, $contact, $isFirstTime, 0, 0);
        }

        // addition to klaviyo
        $this->handleUpdatedContactKlaviyo($oldcontact, $contact);
        // send first vontage message
        $this->handleUpdatedContactVontage($oldcontact, $contact, $minSmsproviderId);
    }

    public function promotionalMsgSendingCheck()
    {
        $estTime = new \DateTime('now', new \DateTimeZone('America/New_York'));

        // Output the formatted time
        $hourest = $estTime->format('H');

        $returnFormat = false;

        if ($hourest >= 9 && $hourest < 19) {
            $returnFormat = true;
        }

        return $returnFormat;
    }
}
