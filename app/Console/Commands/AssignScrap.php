<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ContactScrap;
use Illuminate\Support\Facades\Http;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use DB;

class AssignScrap extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'assign:scrapdata';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Assign Scrap data to leads';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */

	public function handle()
	{
		$pendingBusinesses = Lead::where('is_added_by_bot', 1)->where(function ($query) {
			$query->where('sunbiz_status', 'crawled')
				->orWhere('sunbiz_status', 'failedcrawl');
		})->orderBy('name', 'asc')
			->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url')
			->get();
		$count = 0;

		if ($pendingBusinesses->count() > 0) :
			$bar = $this->output->createProgressBar($pendingBusinesses->count());
			// dd($pendingBusinesses);
			foreach ($pendingBusinesses as $business) {
				$contactsId = [];
				$contacts = Contact::where('lead_id', $business->id)->get();
				$tempContacts = ContactScrap::where('lead_id', $business->id)->get();
				foreach ($contacts as $contact) {
					array_push($contactsId, $contact->id);
				}
				foreach ($tempContacts as $tempcontact) {
					array_push($contactsId, 'temp-' . $tempcontact->id);
				}

				Log::channel('scrap_sunbiz')->info($business->id . ', ');

				$this->migratecommandcontacts($business->id, $contactsId);



				$count++;
				$bar->advance();
				Log::channel('scrap_sunbiz')->info(' assigned successfully.');
			}
			$bar->finish();
			$this->info($count . ' businesses has been assigned successfully.');
		endif;
	}

	public function migratecommandcontacts($currentPageLeadId, $contactsId)
	{

		$leadIds = $contactsId;
		$currentPageLeadId = $currentPageLeadId;

		if (count($leadIds) <= 0) :
			Log::channel('scrap_sunbiz')->info('Please check at least one checkbox to continue.');
			return;
		endif;
		if ($currentPageLeadId <= 0) :
			Log::channel('scrap_sunbiz')->info('Mandatory Parameter missing.PLease contact administrator.');
			return;
		endif;



		// echo $currentPageLeadId;
		// die();

		$intArray = [];
		$tempArray = [];
		foreach ($leadIds as $item) {
			if (is_numeric($item)) {
				$intArray[] = $item;
			} else {
				$item = $this->extractInteger($item);
				$tempArray[] = $item;
			}
		}

		// print_r($intArray);

		$insertedOrUpdatedIds = [];
		if (count($tempArray) > 0 && $currentPageLeadId > 0) {
			$tempCollection = ContactScrap::whereIn('id', $tempArray)->get();



			foreach ($tempCollection as $temps) {

				$contact = Contact::where([
					'lead_id' => $temps->lead_id,
					'c_first_name' => $temps->c_first_name,
					'c_last_name' => $temps->c_last_name,
					'c_full_name' => $temps->c_full_name,
				])->first();

				if ($contact) {
					// Record exists, update it
					$contact->update([
						'c_title' => $temps->c_title,
						'c_full_name' => $temps->c_full_name,
						'added_by_scrap_apis' => 1,
						'prospect_verified' => 'pending'
					]);
					array_push($insertedOrUpdatedIds, $contact->id);
				} else {
					// Record does not exist, insert it
					$contact = Contact::create([
						'lead_id' => $temps->lead_id,
						'c_first_name' => $temps->c_first_name,
						'c_last_name' => $temps->c_last_name,
						'c_title' => $temps->c_title,
						'c_full_name' => $temps->c_full_name,
						'added_by_scrap_apis' => 1,
						'prospect_verified' => 'pending'
					]);

					array_push($insertedOrUpdatedIds, $contact->id);
				}

				// You can use the $lastInsertedOrUpdatedId as needed

				ContactScrap::where('id', $temps->id)->delete();
			}

			// print_r($insertedOrUpdatedIds);

			if (count($insertedOrUpdatedIds) > 0 && count($intArray) > 0 && $currentPageLeadId > 0) {
				if ($currentPageLeadId >= 1) :
					Contact::whereNotIn('id', $insertedOrUpdatedIds)->where('lead_id', $currentPageLeadId)->delete();
				endif;
			}

			if ($currentPageLeadId >= 1) {
				Lead::where('id', $currentPageLeadId)->update([
					'sunbiz_status' => 'migrated',
					'is_added_by_bot' => '2',
					// Add more fields to update as needed
				]);
			}
		}
		Log::channel('scrap_sunbiz')->info('Migration of contacts done.');
		return '';
	}

	function extractInteger($str)
	{
		// Use a regular expression to find the first sequence of digits in the string
		preg_match('/\d+/', $str, $matches);
		// Convert the result to an integer
		return isset($matches[0]) ? intval($matches[0]) : null;
	}
}
