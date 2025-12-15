<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\ScrapApiPlatform;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ScrapDataLabs;
use App\Model\LeadsModel\ScrapOpenPeopleSearch;
use App\Model\LeadsModel\ScrapContactApiPlatform;
use App\Model\LeadsModel\Log;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Model\Dialing;
use App\Model\Agentlog;
use Validator;
use App\Traits\CommonFunctionsTrait;

use View;

class ScrapContactController extends Controller
{

	public function scrapContactView()
	{
		$scrap_vars = [];
		$used_api_platform_id = '';
		$partial_data_response = ScrapContactApiPlatform::checkForPartialData();

		$scrap_partial_data = $partial_data_response['partial_data'];

		if (count($scrap_partial_data) > 0) {
			$used_api_platform_id = $scrap_partial_data[0]->api_platform_id;
		}
		//All platform setting
		$scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($used_api_platform_id);
		if (count($scrap_platform_settings) <= 0)
			$scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings()[0];
		$scrap_vars['platform_name'] = $scrap_platform_settings[0]['platform_name'];
		$scrap_vars['limit'] = 2;
		$scrap_vars['all_scrap'] = self::allRecords();

		return view('scrap_api_platform.scrap-contact', compact('scrap_vars'));
	}

	//Listing view
	// public function callForScrapContact(Request $request)
	// {
	//     try {
	//         $limit = $request->post('limit');
	//         $contact_id = $request->post('contact_id');

	//         $partial_data_response = $this->checkForPartialData($contact_id, $limit, 'call_for_api');
	//         // dd($partial_data_response);
	//         $scrap_partial_data = $partial_data_response['partial_data'];

	//         $used_api_platform_id = '';
	//         $contact_ids = [];
	//         if (count($scrap_partial_data) > 0) {
	//             $used_api_platform_id = $scrap_partial_data[0]->api_platform_id;
	//             $contact_ids = $partial_data_response['contact_ids'];
	//         } else {
	//             $contact_ids = $contact_id;
	//         }

	//         //All platform setting
	//         $scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($used_api_platform_id);

	//         $contactDataForScrapping = $this->getContactDataForScrapping($limit, $contact_ids);

	//         if (count($contactDataForScrapping) > 0 && !empty($scrap_platform_settings)) {
	//             $scrap_platform_settings = $scrap_platform_settings[0];
	//             switch ($scrap_platform_settings['platform_name']) {
	//                 case 'DataLabs':
	//                     $datalabs_resp = ScrapDataLabs::callForDataLabsApi($contactDataForScrapping, $scrap_platform_settings);
	//                     return $datalabs_resp;
	//                     break;
	//                 case 'PeopleSearch':
	//                     $people_search_resp = ScrapOpenPeopleSearch::callForOpenPeopleSearchApi($contactDataForScrapping, $scrap_platform_settings);
	//                     return $people_search_resp;
	//                     break;
	//                 default:
	//                     return 'not found';
	//                     //code block
	//             }
	//         }
	//         return ['status' => 'true', 'data' => [], 'message' => 'No contacts available'];
	//     } catch (\Throwable $err) {
	//         toastr()->error($err);
	//         throw ($err);
	//     }
	// }
	//Listing view
	public function callForScrapContact(Request $request)
	{
		$limit = $request->post('limit');
		$contact_id = $request->post('contact_id');
		$resp = ScrapContactApiPlatform::callForScrapContactApiPlatform($limit, $contact_id);
		return $resp;
	}


	// Get Contact Data For Scrapping
	// public function getContactDataForScrapping($limit, $contact_ids = [])
	// {
	//     if (!empty($contact_ids) && count($contact_ids) > 0) {
	//         $contact_data = Contact::whereIn('id', $contact_ids)->whereIn('prospect_verified', ['pending', 'partial', 'unavailable'])->where('added_by_scrap_apis', 1)->orderBy('id', 'asc')->with('leads')->limit($limit)
	//             // ->toSql();
	//             ->get();
	//     } else {
	//         $contact_data = Contact::where('prospect_verified', 'pending')->where('added_by_scrap_apis', 1)->orderBy('id', 'asc')->with('leads')->limit($limit)
	//             // ->toSql();
	//             ->get();
	//     }
	//     return $contact_data;
	// }

	// public function checkForPartialData($contact_id = '', $limit = '', $type = '')
	// {
	//     $contact_ids = [];
	//     $response = [];
	//     $partial_data_query = DB::table('scrap_contact_api_platforms')->where('merged_status', 'pending');
	//     if ($contact_id != '') {
	//         $partial_data_query = $partial_data_query->where('contact_id', $contact_id)->whereIn('status', ['partial', 'unavailable']);
	//     } else {
	//         $partial_data_query = $partial_data_query->whereIn('status', ['partial', 'unavailable']);
	//     }
	//     if ($limit != '')
	//         $partial_data_query->limit($limit);
	//     $partial_data = $partial_data_query->get();

	//     if (count($partial_data) > 0) {
	//         foreach ($partial_data as $key => $val) {
	//             array_push($contact_ids, $val->contact_id);
	//             if ($type == 'call_for_api') {
	//                 // All platform setting
	//                 $scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($partial_data[0]->api_platform_id);
	//                 if (!empty($scrap_platform_settings))
	//                     DB::table('scrap_contact_api_platforms')->where('id', $val->id)->update(['merged_status' => 'processed_next']);
	//             }
	//         }
	//         $response['contact_ids'] = $contact_ids;
	//     }
	//     $response['partial_data'] = $partial_data;
	//     return $response;
	// }

	public function allRecords()
	{
		$response = ['success' => [], 'partial' => [], 'unavailable' => [], 'not found' => []];

		$all_record = Contact::where('prospect_verified', '!=', 'pending')
			->where('added_by_scrap_apis', 1)
			->with('leads')
			->orderBy('id', 'asc')
			->get();
		// $all_record = DB::select('select * from contacts,leads,scrap_contact_api_platforms where contacts.lead_id = leads.id and contacts.id = scrap_contact_api_platforms.contact_id and contacts.prospect_verified != "pending" and scrap_contact_api_platforms.created_at >= MAX(scrap_contact_api_platforms.created_at) order by scrap_contact_api_platforms.created_at desc');

		// dd($all_ record);
		foreach ($all_record as $record) {
			$response[$record['prospect_verified']][] = $record;
		}
		return $response;
	}


	public function exportCsv($status)
	{
		$filename = 'scrapped_contact-' . time() . '.csv';

		$headers = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => "attachment; filename=\"$filename\"",
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0',
		];
		// $data = ScrapContactApiPlatform::with('contacts', 'contacts.leads')->with('apiPlatform')->where('status', $status)->withTrashed()->get();

		return response()->stream(function () use ($status) {
			$handle = fopen('php://output', 'w');
			// Add CSV headers
			fputcsv($handle, array(
				'Sl No',
				'Status',
				'First Name',
				'Last Name',
				'Lead',
				'Email',
				'Phone',
				'Address',
				'City',
				'Zip'
			));
			// Fetch and process data in chunks
			// $records = ScrapContactApiPlatform::with('contacts', 'contacts.leads')->with('apiPlatform')->where('status', $status)->withTrashed()->get();
			// $records = DB::select('select *, MAX(scrap_contact_api_platforms.created_at) from contacts,leads,scrap_contact_api_platforms where contacts.lead_id = leads.id and contacts.id =scrap_contact_api_platforms.contact_id and contacts.prospect_verified = ? order by scrap_contact_api_platforms.created_at desc', $status);
			// dd($records);
			$records = Contact::where('prospect_verified', $status)
				->with('leads')
				//     ->addSelect(DB::raw('
				//         (select count(*) 
				//         from posts_votes 
				//         where type = 1 
				//         and post_id = posts.id) 
				//         as up_votes
				//     '))
				->orderBy('id', 'asc')
				->get();
			// ScrapContactApiPlatform::with('contacts', 'contacts.leads')->with('apiPlatform')->where('status', $status)->withTrashed->get(function ($records) use ($handle) {
			foreach ($records as $key => $record) {

				// Extract data from each employee.
				$data = array(
					$key + 1,
					$record['prospect_verified'],
					$record['c_first_name'],
					$record['c_last_name'],
					$record['leads']['name'],
					$record['c_email'],
					$record['c_phone'],
					$record['c_address1'],
					$record['c_city'],
					$record['c_zip'],
				);

				// Write data to a CSV file.
				fputcsv($handle, $data);
				// fputcsv($handle, $data);
			}
			// });

			// Close CSV file handle
			fclose($handle);
		}, 200, $headers);
	}
}
