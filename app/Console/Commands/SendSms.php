<?php

namespace App\Console\Commands;

use App\Model\FhinsureLog;
use App\Model\LeadsModel\Contact;
use App\Model\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Vonage\Client;

/**
 * Command to send SMS messages via Vonage
 */
class SendSms extends Command
{
    /** @var string Command signature */
    protected $signature = 'command:smssend';

    /** @var string Command description */
    protected $description = 'Send SMS messages via Vonage';

    // Vonage constants
    private const PROVIDER_FLAG_FHINSURE = 2;
    private const STATUS_SENT = 1;
    private const STATUS_FAILED = 2;
    private const DEFAULT_FROM = '+18882024249';

    /**
     * Execute the console command
     */
    public function handle()
    {
        try {
            $vonageClient = $this->createVonageClient();
            $messages = $this->getPendingMessages();

            foreach ($messages as $message) {
                $this->processMessage($message, $vonageClient);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred at line ' . $e->getLine() . ' : ' . $e->getMessage());
        }
    }

    /**
     * Create Vonage client
     */
    private function createVonageClient()
    {
        $key = env('VONAGE_KEY');
        $secret = env('VONAGE_SECRET');

        return new Client(new \Vonage\Client\Credentials\Basic($key, $secret));
    }

    /**
     * Get Vonage sender number
     */
    private function getVonageFromNumber(): string
    {
        return env('VONAGE_FROM') ?? self::DEFAULT_FROM;
    }

    /**
     * Get pending messages to send
     */
    private function getPendingMessages()
    {
        return Message::where('chat_type', 'outbound')
            ->where('chat_sms_sent_status', '0')
            ->orderBy('id', 'desc')
            ->limit(1)
            ->get();
    }

    /**
     * Process single message
     */
    private function processMessage($message, $vonageClient): void
    {
        $toMobile = $this->getRecipientPhone($message);

        if (empty($toMobile)) {
            $this->updateMessageStatus($message->id, self::STATUS_FAILED);
            return;
        }

        $response = $this->sendVonageMessage($vonageClient, $toMobile, $message->content);
        $this->updateMessageStatus($message->id, $response);
    }

    /**
     * Get recipient phone number
     */
    private function getRecipientPhone($message): string
    {
        if (!empty($message->through_sms_provider_flag) && $message->through_sms_provider_flag == self::PROVIDER_FLAG_FHINSURE) {
            return $this->getFhinsurePhone($message->newsletter_id);
        }

        return $this->getContactPhone($message->contact_id);
    }

    /**
     * Get phone from FhinsureLog
     */
    private function getFhinsurePhone(int $newsletterId): string
    {
        $contact = FhinsureLog::select('phone')->where('id', $newsletterId)->first();
        return $contact->phone ?? '';
    }

    /**
     * Get phone from Contact
     */
    private function getContactPhone(int $contactId): string
    {
        $contact = Contact::select('c_phone')->where('id', $contactId)->first();
        return $contact->c_phone ?? '';
    }

    /**
     * Send Vonage message
     */
    private function sendVonageMessage($client, string $toMobile, string $text)
    {
        $response = $client->message()->send([
            'to' => '+1 ' . $toMobile,
            'from' => $this->getVonageFromNumber(),
            'text' => $text,
        ]);

        return $response->current();
    }

    /**
     * Update message status
     */
    private function updateMessageStatus(int $messageId, $response): void
    {
        $status = (isset($response['status']) && $response['status'] == 0)
            ? self::STATUS_SENT
            : self::STATUS_FAILED;

        Message::where('id', $messageId)->update([
            'chat_sms_sent_status' => $status,
            'vonageResponse' => json_encode($response)
        ]);
    }
}
