<?php

namespace App\Jobs;

use App\Model\LeadsModel\Contact;
use App\Model\SmsProvider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsContactUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $smsProviderData;
    public $updateFlag;

    public function __construct(SmsProvider $smsProviderData, $updateFlag = '')
    {
        $this->smsProviderData = $smsProviderData;
        $this->updateFlag = $updateFlag;
    }

    public function handle(): void
    {
        $totalDelayMinutes = $this->calculateTotalDelay($this->smsProviderData);
        $currentTime = Carbon::now();

        Contact::select('id', 'current_sent_smsprovider_id', 'first_sms_date_time', 'next_sms_date_time',
            'skip_response_step', 'respond_to_cron_flag')
            ->where(function ($query) {
                $query->whereNotNull('c_phone')->where('c_phone', '<>', '')
                    ->orWhere(function ($q) {
                        $q->whereNotNull('c_email')->where('c_email', '<>', '');
                    });
            })
            ->whereNotNull('first_sms_date_time')
            ->whereNotNull('next_sms_date_time')
            ->chunk(500, function ($contacts) use ($totalDelayMinutes, $currentTime) {
                foreach ($contacts as $contact) {
                    $this->updateContactNextSms($contact, $totalDelayMinutes, $currentTime);
                }
            });
    }

    private function calculateTotalDelay(SmsProvider $smsProvider): int
    {
        return ($smsProvider->day_delay * 24 * 60) + $smsProvider->minute_delay;
    }

    private function updateContactNextSms($contact, int $totalDelayMinutes, Carbon $currentTime): void
    {
        $firstDateTime = Carbon::parse($contact->first_sms_date_time);
        $nextDateTime = Carbon::parse($contact->next_sms_date_time);
        $newNextDateTime = $firstDateTime->copy()->addMinutes($totalDelayMinutes);

        if ($newNextDateTime <= $currentTime) {
            return; // Already past, nothing to update
        }

        if ($newNextDateTime > $nextDateTime || $newNextDateTime < $nextDateTime) {
            if ($contact->respond_to_cron_flag == 1) {
                $contact->update([
                    'respond_to_cron_flag' => 0,
                    'next_sms_date_time' => $newNextDateTime,
                ]);
            }
        }
    }
}
