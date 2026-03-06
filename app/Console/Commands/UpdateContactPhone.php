<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command to update contact phone numbers to exclude +1 prefix
 */
class UpdateContactPhone extends Command
{
    /** @var string Command signature */
    protected $signature = 'command:contact-phone-update';

    /** @var string Command description */
    protected $description = 'Update contact phone to exclude +1';

    // Processing constants
    private const CHUNK_SIZE = 200;
    private const MIN_PHONE_LENGTH = 10;

    /**
     * Execute the console command
     */
    public function handle(): void
    {
        try {
            $this->processContacts();
        } catch (\Exception $e) {
            Log::error('Failed to update contact: ' . $e->getMessage());
        }
    }

    /**
     * Process contacts in chunks
     */
    private function processContacts(): void
    {
        Contact::chunk(self::CHUNK_SIZE, function ($contacts) {
            $updateData = [];

            foreach ($contacts as $contact) {
                $updateData[] = $this->prepareContactUpdateData($contact);
            }

            Contact::upsert(
                $updateData,
                ['id'],
                ['c_phone', 'c_phone_updated', 'c_phone_update_status']
            );
        });
    }

    /**
     * Prepare contact update data
     */
    private function prepareContactUpdateData($contact)
    {
        $id = $contact->id;
        $phone = $contact->c_phone;

        if (!$phone) {
            return [
                'id' => $id,
                'c_phone' => $phone,
                'c_phone_updated' => 0,
                'c_phone_update_status' => 'Phone no not added.',
            ];
        }

        return $this->processPhoneNumber($id, $phone);
    }

    /**
     * Process phone number to remove +1 prefix
     */
    private function processPhoneNumber(int $id, string $phone): array
    {
        if (!str_contains($phone, '+1')) {
            return [
                'id' => $id,
                'c_phone' => $phone,
                'c_phone_updated' => 0,
                'c_phone_update_status' => 'Phone no does not contain +1',
            ];
        }

        $cleanPhone = str_replace('+1', '', $phone);

        if (strlen($cleanPhone) < self::MIN_PHONE_LENGTH) {
            return [
                'id' => $id,
                'c_phone' => $cleanPhone,
                'c_phone_updated' => 0,
                'c_phone_update_status' => 'Not Updated! Phone length is ' . strlen($cleanPhone) . ' (excluding +1).',
            ];
        }

        return [
            'id' => $id,
            'c_phone' => $cleanPhone,
            'c_phone_updated' => 1,
            'c_phone_update_status' => 'Updated successfully.',
        ];
    }
}
