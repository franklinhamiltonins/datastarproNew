<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ContactScrap;
use App\Model\LeadsModel\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// Command to assign scraped contact data to leads.
class AssignScrap extends Command
{
    /** @var string Command signature */
    protected $signature = 'assign:scrapdata';

    /** @var string Command description */
    protected $description = 'Assign Scrap data to leads';

    // Status constants for lead sunbiz processing
    private const STATUS_CRAWLED = 'crawled';
    private const STATUS_FAILED_CRAWL = 'failedcrawl';
    private const STATUS_MIGRATED = 'migrated';
    private const BOT_ADDED = 1;
    private const BOT_MIGRATED = 2;

    // Execute the console command.
    public function handle()
    {
        return $this->executeCommand();
    }

    // Execute the assign scrap command.
    private function executeCommand()
    {
        $pendingBusinesses = $this->fetchPendingLeads();

        if ($pendingBusinesses->isEmpty()) {
            return $this->info('No pending businesses found.');
        }

        return $this->runMigrationLoop($pendingBusinesses);
    }

    // Fetch leads that need scrap data assigned.
    private function fetchPendingLeads()
    {
        return Lead::where('is_added_by_bot', self::BOT_ADDED)
            ->where(function ($query) {
                return $this->applyStatusFilter($query);
            })
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url')
            ->get();
    }

    // Apply status filter to query.
    private function applyStatusFilter($query)
    {
        return $query->where('sunbiz_status', self::STATUS_CRAWLED)
            ->orWhere('sunbiz_status', self::STATUS_FAILED_CRAWL);
    }


    private function runMigrationLoop($pendingBusinesses)
    {
        $bar = $this->initProgressBar($pendingBusinesses);
        $count = $this->processBusinessesLoop($pendingBusinesses, $bar);
        $this->finishProgressBar($bar);

        return $this->info($count . ' businesses assigned.');
    }


    private function initProgressBar($pendingBusinesses)
    {
        return $this->output->createProgressBar($pendingBusinesses->count());
    }

    // Process businesses in loop.
    private function processBusinessesLoop($pendingBusinesses, $bar)
    {
        $count = 0;

        foreach ($pendingBusinesses as $business) {
            $this->processSingleBusiness($business);
            $count++;
            $bar->advance();
        }

        return $count;
    }


    private function finishProgressBar($bar)
    {
        $bar->finish();
    }


    private function processSingleBusiness($business)
    {
        $contactsId = $this->collectAllContactIds($business->id);
        $this->logBusinessStart($business->id);
        $this->migrateContacts($business->id, $contactsId);
        Log::channel('scrap_sunbiz')->info(' assigned successfully.');
    }


    private function logBusinessStart($businessId)
    {
        Log::channel('scrap_sunbiz')->info($businessId . ', ');
    }


    private function collectAllContactIds($leadId)
    {
        $contactIds = $this->getContactIds($leadId);
        $tempContactIds = $this->getTempContactIds($leadId);

        return array_merge($contactIds, $tempContactIds);
    }


    private function getContactIds($leadId)
    {
        $ids = [];
        $contacts = Contact::where('lead_id', $leadId)->get();

        foreach ($contacts as $contact) {
            $ids[] = $contact->id;
        }

        return $ids;
    }

    // Get temp contact IDs from ContactScrap table.
    private function getTempContactIds($leadId)
    {
        $ids = [];
        $tempContacts = ContactScrap::where('lead_id', $leadId)->get();

        foreach ($tempContacts as $tempcontact) {
            $ids[] = 'temp-' . $tempcontact->id;
        }

        return $ids;
    }


    public function migrateContacts($leadId, $contactIds)
    {
        if ($this->validateMigrationParams($leadId, $contactIds)) {
            return '';
        }

        $separatedIds = $this->separateContactIds($contactIds);
        $insertedOrUpdatedIds = $this->processTempContacts(
            $separatedIds['temp'],
            $leadId
        );
        $this->finalizeMigration($leadId, $insertedOrUpdatedIds, $separatedIds);

        return '';
    }

    // Finalize migration by cleanup and status update.
    private function finalizeMigration($leadId, $insertedOrUpdatedIds, $separatedIds)
    {
        $this->cleanupAndUpdateStatus(
            $leadId,
            $insertedOrUpdatedIds,
            $separatedIds['numeric']
        );
        Log::channel('scrap_sunbiz')->info('Migration of contacts done.');
    }


    private function validateMigrationParams($leadId, $contactIds)
    {
        if ($this->isContactIdsEmpty($contactIds)) {
            return true;
        }

        if ($this->isLeadIdInvalid($leadId)) {
            return true;
        }

        return false;
    }


    private function isContactIdsEmpty($contactIds)
    {
        if (empty($contactIds)) {
            Log::channel('scrap_sunbiz')
                ->info('Please check at least one checkbox to continue.');

            return true;
        }

        return false;
    }

    private function isLeadIdInvalid($leadId)
    {
        if ($leadId <= 0) {
            Log::channel('scrap_sunbiz')
                ->info('Mandatory Parameter missing. Please contact administrator.');

            return true;
        }

        return false;
    }

    // Cleanup and update lead status in one call.
    private function cleanupAndUpdateStatus($leadId, $insertedOrUpdatedIds, $numericIds)
    {
        $this->cleanupContacts($leadId, $insertedOrUpdatedIds, $numericIds);
        $this->updateLeadStatus($leadId);
    }

    // Separate contact IDs into numeric and temp arrays.
    private function separateContactIds($contactIds)
    {
        $intArray = $this->extractNumericIds($contactIds);
        $tempArray = $this->extractTempIds($contactIds);

        return ['numeric' => $intArray, 'temp' => $tempArray];
    }

    private function extractNumericIds($contactIds)
    {
        $intArray = [];

        foreach ($contactIds as $item) {
            if (is_numeric($item)) {
                $intArray[] = $item;
            }
        }

        return $intArray;
    }


    private function extractTempIds($contactIds)
    {
        $tempArray = [];

        foreach ($contactIds as $item) {
            if (!is_numeric($item)) {
                $tempArray[] = $this->extractInteger($item);
            }
        }

        return $tempArray;
    }

    private function processTempContacts($tempArray, $leadId)
    {
        if ($this->hasNoTempContacts($tempArray)) {
            return [];
        }

        $tempCollection = ContactScrap::whereIn('id', $tempArray)->get();

        return $this->migrateEachTempContact($tempCollection);
    }

    // Check if there are no temp contacts.
    private function hasNoTempContacts($tempArray)
    {
        return empty($tempArray);
    }

    // Migrate each temp contact to Contact table.
    private function migrateEachTempContact($tempCollection)
    {
        $insertedOrUpdatedIds = [];

        foreach ($tempCollection as $temps) {
            $id = $this->migrateSingleTempContact($temps);
            $insertedOrUpdatedIds[] = $id;
        }

        return $insertedOrUpdatedIds;
    }


    private function migrateSingleTempContact($temps)
    {
        $contact = $this->findExistingContact($temps);

        if ($contact) {
            return $this->updateAndReturnId($contact, $temps);
        }

        return $this->createAndReturnId($temps);
    }


    private function updateAndReturnId($contact, $temps)
    {
        $this->updateExistingContact($contact, $temps);
        $this->deleteTempContact($temps->id);

        return $contact->id;
    }


    private function createAndReturnId($temps)
    {
        $newContact = $this->createNewContact($temps);
        $this->deleteTempContact($temps->id);

        return $newContact->id;
    }


    private function deleteTempContact($id)
    {
        ContactScrap::where('id', $id)->delete();
    }


    private function findExistingContact($temps)
    {
        return Contact::where([
            'lead_id' => $temps->lead_id,
            'c_first_name' => $temps->c_first_name,
            'c_last_name' => $temps->c_last_name,
            'c_full_name' => $temps->c_full_name,
        ])->first();
    }


    private function updateExistingContact($contact, $temps)
    {
        $contact->update([
            'c_title' => $temps->c_title,
            'c_full_name' => $temps->c_full_name,
            'added_by_scrap_apis' => 1,
            'prospect_verified' => 'pending',
        ]);
    }

    private function createNewContact($temps)
    {
        return Contact::create([
            'lead_id' => $temps->lead_id,
            'c_first_name' => $temps->c_first_name,
            'c_last_name' => $temps->c_last_name,
            'c_title' => $temps->c_title,
            'c_full_name' => $temps->c_full_name,
            'added_by_scrap_apis' => 1,
            'prospect_verified' => 'pending',
        ]);
    }

    // Delete contacts that were not migrated.
    private function cleanupContacts($leadId, $insertedOrUpdatedIds, $numericIds)
    {
        if (!empty($insertedOrUpdatedIds) && !empty($numericIds)) {
            Contact::whereNotIn('id', $insertedOrUpdatedIds)
                ->where('lead_id', $leadId)
                ->delete();
        }
    }

    private function updateLeadStatus($leadId)
    {
        if ($leadId >= 1) {
            Lead::where('id', $leadId)->update([
                'sunbiz_status' => self::STATUS_MIGRATED,
                'is_added_by_bot' => self::BOT_MIGRATED,
            ]);
        }
    }

    public function extractInteger($str)
    {
        preg_match('/\d+/', $str, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }
}
