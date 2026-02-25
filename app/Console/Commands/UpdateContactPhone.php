<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateContactPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:contact-phone-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update contact phone to exclude +1';

    public function handle():void
    {
        try {
            Contact::chunk(200, function ($contacts) {
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

        } catch (\Exception $e) {
            Log::error('Failed to update contact: ' . $e->getMessage());
        }
    }

    // Prepare a single contact update array
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

        // Initialize defaults
        $cleanPhone = $phone;
        $updated = 0;
        $status = '';

        // Check if phone contains +1
        if (!str_contains($phone, '+1')) {
            $status = 'Phone no does not contain +1';
        } else {
            // Strip +1
            $cleanPhone = str_replace('+1', '', $phone);

            if (strlen($cleanPhone) < 10) {
                $status = 'Not Updated! Phone length is ' . strlen($cleanPhone) . ' (excluding +1).';
            } else {
                $updated = 1;
                $status = 'Updated successfully.';
            }
        }

        return [
            'id' => $id,
            'c_phone' => $cleanPhone,
            'c_phone_updated' => $updated,
            'c_phone_update_status' => $status,
        ];
    }

}
