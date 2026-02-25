<?php

namespace App\Traits;

use App\Model\Setting;
use App\Model\SmtpConfiguration;
use Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

trait SMTPRelatedTrait
{
    /**
     * Get mail configuration conditions
     */
    protected function getMailConfigConditions(): array
    {
        return [
            ['username', '!=', ''],
            ['password', '!=', ''],
            ['host', '!=', ''],
            ['port', '!=', ''],
            ['encryption', '!=', ''],
            ['from_name', '!=', ''],
        ];
    }

    public function checkMailConfigurationUserWise($mailAgentId)
    {
        $whereCond = $this->getMailConfigConditions();

        return SmtpConfiguration::where('user_id', $mailAgentId)
            ->where($whereCond)
            ->count();
    }

    /**
     * Build SMTP configuration for user
     */
    protected function buildUserSmtpConfig($configuration): array
    {
        $password = Crypt::decryptString("$configuration->password");

        return [
            'driver' => 'smtp',
            'transport' => 'smtp',
            'host' => $configuration->host,
            'port' => $configuration->port,
            'username' => $configuration->username,
            'password' => "$password",
            'encryption' => $configuration->encryption,
            'from' => ['address' => $configuration->username, 'name' => $configuration->from_name],
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        ];
    }

    public function setDynamicSMTPUserWise($mailAgentId)
    {
        $smtpData = $this->checkMailConfigurationUserWise($mailAgentId);
        if ($smtpData > 0) {
            $configuration = SmtpConfiguration::where('user_id', $mailAgentId)->first();
            $config = $this->buildUserSmtpConfig($configuration);
            Config::set('mail', $config);
        }
    }

    public function sendSimpleNotificationMail($mailAgentId, $subject, $mailBody,$file=NULL)
    {
        try {
            $recipientData = $this->getRecipientData('notify_email');
            if (! $recipientData) {
                return;
            }
            [$recipientEmail, $ccEmails] = $recipientData;

            $this->setDynamicSMTPUserWise($mailAgentId);

            $this->sendEmail($recipientEmail, $ccEmails, $subject, $mailBody,$file);

        } catch (\Exception $e) {
            Log::error('Agent reassign error while sending mail: '.$e->getMessage());
        }
    }

    public function getRecipientData($getColumm)
    {
        $allowed = ['notify_email', 'notify_email_pipeline', 'mail_fetching_error_notification_email'];
        if (! in_array($getColumm, $allowed)) {
            return null;
        }
        $setting = Setting::selectRaw("$getColumm as emails")->first();
        if (! $setting || empty($setting->emails)) {
            return null;
        }
        $emails = array_filter(array_map('trim', explode(',', $setting->emails)));
        if (empty($emails)) {
            return null;
        }
        $recipientEmail = $emails[0];
        $ccEmails = array_slice($emails, 1);

        return [$recipientEmail, $ccEmails];
    }

    public function sendEmail($recipientEmail, $ccEmails, $subject, $mailBody,$file)
    {
        Mail::send([], [], function ($message) use ($recipientEmail, $ccEmails, $subject, $mailBody,$file) {
            $message->to($recipientEmail);
            if (! empty($ccEmails)) {
                $message->cc($ccEmails);
            }
            if (!empty($file['filePath']) && !empty($file['fileName'])) {
                $message->attach($file['filePath'], [
                    'as' => $file['filePath'],
                    'mime' => $file['mime'],
                ]);
            }
            $message->subject($subject)
                ->html($mailBody);
        });
    }

    // Delete CSV file after sending
    public function deleteCsv(string $filePath): void
    {
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    // Send report email with CSV attachment
    public function sendReportEmail(string $subject, string $body, string $filePath, string $fileName): void
    {
        $file = [
            "filePath" => $filePath,
            "fileName" => $fileName,
            "mime" => 'text/csv',
        ];

        $this->sendSimpleNotificationMail($this->mailAgentId, $subject, $body, $file);
    }
}
