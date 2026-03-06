<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use DB;
use Illuminate\Console\Command;

/**
 * Command to update contact information
 */
class ContactRemoval extends Command
{
    /** @var string Command signature */
    protected $signature = 'contact:removal {tablename} {actiontype}';

    /** @var string Command description */
    protected $description = 'update contact information - actiontype = 1 removal of field, actiontype = 2 update of fields';

    // Status constant
    private const VERIFIED_STATUS = 'Verified';

    public function handle()
    {
        $tableName = $this->argument('tablename');
        $actionType = $this->argument('actiontype');

        while (DB::table($tableName)->where('status', 0)->count() > 0) {
            DB::table($tableName)
                ->where('status', 0)
                ->orderBy('id')
                ->chunk(500, function ($nonMatchedData) use ($tableName, $actionType) {
                    foreach ($nonMatchedData as $key => $nonMatched) {
                        $contact = Contact::withTrashed()->find($nonMatched->Contact_id);
                        if ($contact) {
                            if ($actionType == 1) {
                                $this->blankContactColumnData($contact);
                            } elseif ($actionType == 2) {
                                $this->updateContactColumnData($contact, $nonMatched);
                            }

                            $this->updateNonMatchedDataStatus($tableName, $nonMatched->id, 1);
                        } else {
                            $this->updateNonMatchedDataStatus($tableName, $nonMatched->id, 2);
                        }
                    }
                });
        }

        $this->info('Success');

        return 0;
    }

    /**
     * Blank contact column data
     */
    private function blankContactColumnData($contact)
    {
        $contact = Contact::withTrashed()->find($contact->id);
        if ($contact) {
            $contact->c_address1 = null;
            $contact->c_address2 = null;
            $contact->c_city = null;
            $contact->c_state = null;
            $contact->c_zip = null;
            $contact->c_county = null;
            $contact->verified_status = self::VERIFIED_STATUS;
            $contact->save();
        }
    }

    /**
     * Update contact column data
     */
    private function updateContactColumnData($contact, $nonMatched)
    {
        $contact = Contact::withTrashed()->find($contact->id);
        if ($contact) {
            $contact->verified_status = self::VERIFIED_STATUS;
            $contact->save();
        }
    }

    /**
     * Beautify phone number format
     */
    private function beautifyPhoneNumberFormat($phoneNumber)
    {
        return preg_replace('/\D/', '', $phoneNumber);
    }

    /**
     * Update non-matched data status
     */
    private function updateNonMatchedDataStatus($tableName, $nonMatchedId, $status)
    {
        DB::table($tableName)
            ->where('status', 0)
            ->where('id', $nonMatchedId)
            ->update(['status' => $status]);

        return 0;
    }
}
