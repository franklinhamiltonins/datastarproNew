<?php

// namespace App\Http\Controllers;

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Campaign;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\ContactScrap;
use App\Model\LeadsModel\Contact;
use App\Model\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use App\Model\ScrapCounty;
use Illuminate\Support\Facades\URL;
use App\Model\ScrapCity;
use App\Model\LeadsModel\ScrapSunbizLeads;
use App\Traits\CommonFunctionsTrait;
use PhpParser\Node\Expr\Cast\Object_;
use Goutte\Client;

class ScrapController extends Controller
{
	use CommonFunctionsTrait;

	public function scrap($search_terms = 'condo', $geo_location_terms = 'Jacksonville', $state_code = 'FL')
	{
		$total = 0;
		$each = 0;
		$paginate_total = 0;
		$paginate = 1;
		$arr = [];
		$data = [];
		$crawler_str = '';
		do {
			$crawler_str = 'https://www.yellowpages.com/search?search_terms=' . $search_terms . '&geo_location_terms=' . $geo_location_terms . '%2C%20' . $state_code;

			if ($paginate > 1)
				$crawler_str .= '&page=' . $paginate;
			$response = Http::get($crawler_str);
			array_push($arr, $crawler_str);
			// Check if request was successful
			if ($response->successful()) {
				// Create a new Crawler instance
				$crawler = new Crawler($response->body());

				if ($paginate_total == 0) {
					$paginationStr = $crawler->filter('[class^="showing-count"]')->text();
					$pieces = explode(" ", $paginationStr);

					$total = (int) $pieces[3]; //30
					$each = (int) explode('-', $pieces[1])[1];

					$paginate_total = (int)ceil($total / $each);
				}


				$crawler->filter('[class^="result"][id^="lid-"]')->each(function ($node) use (&$data) {
					$businessName = $node->filter('h2.n')->text();
					$businessName = preg_replace('/^\d+\.\s*/', '', $businessName);
					$phone = $node->filter('.phones.phone.primary')->text();
					// echo $node->filter('.street-address')->count() > 0 ? $node->filter('.street-address')->text() : '';
					$streetAddress = $node->filter('.street-address')->count() > 0 ? $node->filter('.street-address')->text() : '';
					$locality = $node->filter('.locality')->count() > 0 ? $node->filter('.locality')->text() : '';

					preg_match('/([^,]+),\s*(\w+)\s+(\d+)/', $locality, $matches);
					$city = $matches[1] ?? '';
					$zipCode = $matches[2] ?? '';
					$state = $matches[3] ?? '';

					// Store name and h2 text in the data array
					$data[] = [
						'businessName' => $businessName,
						'phone' => $phone,
						'streetAddress' => $streetAddress,
						'city' => $city,
						'zipCode' => $zipCode,
						'state' => $state,
						'locality' => $locality,
					];
				});
				$paginate++;
			} else {
				// Handle failed request
				return response()->json(['error' => 'Failed to fetch website data'], 500);
			}
		} while ($paginate <= $paginate_total);
		return Lead::evaluateCrawlerLeads($data);
	}





	public function scrapSunbizGetLeads(Request $request)
	{
		$vars = array();
		return view('scrap.sunbiz_index', compact($vars));
	}

	public function updateSingleBusinessName(Request $request)
	{
		$leadDetail = Lead::find($request->lead_id);
		$leadId = $leadDetail->id;

		$lead_slug = '';
		if ($leadDetail->name !== $request->lead_name) {
			$lead_slug = $this->generateSlug([$leadDetail->type, $request->lead_name, $leadDetail->city, $leadDetail->zip]);

			$request->lead_name = $this->removeSpecialCharacters($request->lead_name);

			if ($lead_slug) { // based on slug allow addition of business in db
				$slugExistance = $this->checkLeadSlugExistanceWithDistance($lead_slug, $leadDetail->latitude, $leadDetail->longitude);
				$leadDetail->lead_slug = $lead_slug;
				if (is_array($slugExistance) && isset($slugExistance["existanceCount"]) && $slugExistance['existanceCount'] > 0) {
					return ['status' => 'error', 'message' => implode('</br>', $slugExistance['message'])];
					//  return back()->withErrors($validator)->withInput();
				}
			}
		}

		$updateLead = Lead::where('id', $request->lead_id)->update(['name' => $request->lead_name, 'lead_slug' => $lead_slug]);
		if ($updateLead) {
			return ['status' => 'success', 'message' => 'Business name updated successfully.'];
		}
		return ['status' => 'error', 'message' => 'An error occured.Please contact administrator.'];
	}




	public function getScrapSunbizGetLeadsApi_original(Request $request)
	{
		$is_admin_user = auth()->user()->can('agent-create');
		$start = $request->input('start', 0);
		$length = $request->input('length', 10);
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$currentUrl = URL::to('/');
		$search_value = $request->input('search')['value'] ?? null;


		$totalRecords = Lead::where('is_added_by_bot', 1);
		$pendingBusinessesQuery = Lead::where('is_added_by_bot', 1);

		$totalRecords->where(function ($query) {
			$query->where('sunbiz_status', 'crawled')
				->orWhere('sunbiz_status', 'failedcrawl');
		})->count();


		$pendingBusinessesQuery->where(function ($query) {
			$query->where('sunbiz_status', 'crawled')
				->orWhere('sunbiz_status', 'failedcrawl');
		});

		// echo $pendingBusinessesQuery->toSql();

		$pendingBusinessesQuery->with('contactscraps')
			->orderBy($filter_on_column_name, $order_by)
			->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url');

		$filteredRecords = $pendingBusinessesQuery->count();

		// $pendingBusinessesQuery = Lead::where('is_added_by_bot', 1)->where(function ($query) {
		// 	$query->where('sunbiz_status', 'crawled')
		// 		->orWhere('sunbiz_status', 'failedcrawl');
		// })->with('contactscraps')
		// 	->orderBy($filter_on_column_name, $order_by)
		// 	->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url');

		return datatables()->eloquent($pendingBusinessesQuery)
			->addIndexColumn()
			->addColumn('name', function ($business) use ($currentUrl) {
				return isset($business->name)
					? '<span id="businessName_' . $business->id . '"><a href="' . $currentUrl . '/leads/edit/' . $business->id . '" target="_blank">' . $business->name . '</a></span>  <a href="javascript:void(0)" class="edit_business_lead editbtn' . $business->id . '" data-id="' . $business->id . '" ><i class="fas fa-pen"></i></a>'
					: '';
			})
			->addColumn('list_url', function ($business) {
				return isset($business->sunbiz_list_url)
					? '<a href="' . $business['sunbiz_list_url'] . '" target="_blank">Listing Url</a>'
					: '';
			})
			->addColumn('details_url', function ($business) {
				return isset($business->sunbiz_details_url) && !empty($business->sunbiz_details_url)
					? '<a href="' . $business['sunbiz_details_url'] . '" target="_blank">Details Url</a>'
					: '';
			})
			->addColumn('contacts', function ($business) {
				return view('scrap.partials.members', ['members' => $business->contactscraps, 'lead_id' => $business->id])->render();
			})
			->addColumn('scrap', function ($business) {
				return view('scrap.partials.actions', ['id' => $business->id, 'list_url' => $business->list_url, 'lead_id' => $business->id])->render();
			})
			->addColumn('actions', function ($business) {
				return  '<a href="/scrap/compare/' . $business->id . '" >Assign</a>';
			})
			->rawColumns(['name', 'list_url', 'details_url', 'contacts', 'scrap', 'fetch_contacts', 'actions'])
			->with('recordsTotal', $totalRecords)
			->make(true);
	}


	public function getScrapSunbizGetLeadsApi(Request $request)
	{
		$is_admin_user = auth()->user()->can('agent-create');
		$start = $request->input('start', 0);
		$length = $request->input('length', 10);
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$currentUrl = URL::to('/');
		$search_value = $request->input('search')['value'] ?? null;
	
		// Fetch total records
		$totalRecords = Lead::where('is_added_by_bot', 1)
			->where(function ($query) {
				$query->where('sunbiz_status', 'crawled')
					->orWhere('sunbiz_status', 'failedcrawl');
			})->count();
	
		// Build the pending businesses query
		$pendingBusinessesQuery = Lead::where('is_added_by_bot', 1)
			->where(function ($query) {
				$query->where('sunbiz_status', 'crawled')
					->orWhere('sunbiz_status', 'failedcrawl');
			});
	
		// Apply search filter
		if (!empty($search_value)) {
			$pendingBusinessesQuery->where('name', 'like', '%' . $search_value . '%');
		}
	
		$pendingBusinessesQuery->with('contactscraps')
			->orderBy($filter_on_column_name, $order_by)
			->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url');
	
		// Get the count of filtered records
		$filteredRecords = $pendingBusinessesQuery->count();
	
		return datatables()->eloquent($pendingBusinessesQuery)
			->addIndexColumn()
			->addColumn('name', function ($business) use ($currentUrl) {
				return isset($business->name)
					? '<span id="businessName_' . $business->id . '"><a href="' . $currentUrl . '/leads/edit/' . $business->id . '" target="_blank">' . $business->name . '</a></span>  <a href="javascript:void(0)" class="edit_business_lead editbtn' . $business->id . '" data-id="' . $business->id . '" ><i class="fas fa-pen"></i></a>'
					: '';
			})
			->addColumn('list_url', function ($business) {
				return isset($business->sunbiz_list_url)
					? '<a href="' . $business['sunbiz_list_url'] . '" target="_blank">Listing Url</a>'
					: '';
			})
			->addColumn('details_url', function ($business) {
				return isset($business->sunbiz_details_url) && !empty($business->sunbiz_details_url)
					? '<a href="' . $business['sunbiz_details_url'] . '" target="_blank">Details Url</a>'
					: '';
			})
			->addColumn('contacts', function ($business) {
				return view('scrap.partials.members', ['members' => $business->contactscraps, 'lead_id' => $business->id])->render();
			})
			->addColumn('scrap', function ($business) {
				return view('scrap.partials.actions', ['id' => $business->id, 'list_url' => $business->list_url, 'lead_id' => $business->id])->render();
			})
			->addColumn('actions', function ($business) {
				return '<a href="/scrap/compare/' . $business->id . '">Assign</a> / <a href="javascript:void(0)" data-id="'.$business->id.'" class="delete_button" >Delete<a/>';
			})
			->rawColumns(['name', 'list_url', 'details_url', 'contacts', 'scrap', 'actions'])
			->filter(function ($query) {
				if ($keyword = request()->input('search.value')) {
					return $query->where('name', 'like', '%' . $keyword . '%');
				}

				return $query;
			})
			->with('recordsTotal', $totalRecords)
			->with('recordsFiltered', $filteredRecords)
			->make(true);
	}

	public function scrapSunbizdeleteLeads(Request $request)
	{
		try {
			if(empty($request->selectedValues)){
				return response()->json(['status' => false, 'message' => 'Please select Business Name']);
			}

			if(is_array($request->selectedValues)){
				$selectedValues = $request->selectedValues;
			}
			else{
				$selectedValues = explode(',', $request->selectedValues);
			}

			// echo "<pre>";print_r($selectedValues);exit;

			Lead::where('is_added_by_bot', 1)
			->where(function ($query) {
				$query->where('sunbiz_status', 'crawled')
					->orWhere('sunbiz_status', 'failedcrawl');
			})
			->whereIn('id',$selectedValues)
			->delete();

			DB::table('leads_files')->whereIn('lead_id',$selectedValues)->delete();

			return response()->json(['status' => true, 'message' => 'Contacts Scrap Removed successfully!']);
		} catch (\Exception $e) {
			// Return error response
			return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
		}
	}
	


	public function comparecontacts(Request $request)
	{

		$contacts = Contact::where('lead_id', $request->id)->get();
		$tempContacts = ContactScrap::where('lead_id', $request->id)->get();
		if ($contacts->count() <= 0 && $tempContacts->count() <= 0) :
			toastr()->error("No contacts exists for respective lead.");
			return back();
		endif;
		$contactsArr = array($contacts, $tempContacts);
		return view('scrap.assign_contacts', compact('contacts', 'tempContacts'));
	}





	public function calculateSimilarity($lead_name, $business_name, $entity_name_probability_arr)
	{
		$similarity = 0;
		if (strpos($business_name, $lead_name) !== false) {
			$similarity += 0.5;
		}
		foreach ($entity_name_probability_arr as $entity) {
			if (strpos($business_name, $entity) !== false) {
				$similarity += 0.1;
			}
		}
		return $similarity;
	}


	public function getDataBySunbizUrl(Request $request)
	{
		$url = $request->details_url;
		$lead_id = $request->lead_id;
		if (empty($url) && $lead_id <= 0) {
			return ['status' => 'error', 'message' => 'Parameters mismatch. Please contact administrator.'];
		}
		$client = new Client();
		$crawler = $client->request('GET', $url);

		if (!$crawler) {
			return ['status' => 'error', 'message' => 'Nothing crawled from the provided link.'];
		}

		$finalArr = [];

		$membersNames = $data = $members = [];
		$memberIndex = $sectionIndex = 0;

		$sections = $crawler->filter('.detailSection');

		$crawler->filter('span')->each(function (Crawler $spanNode) use (&$finalArr, &$data) {
			$text = $spanNode->text();
			$data[] = $text;
			if ($text === "Principal Address") {
				$finalArr['principal_address'] = $spanNode->nextAll()->text();
			} elseif ($text === "Mailing Address") {
				$finalArr['mailing_address'] = $spanNode->nextAll()->text();
			}
		});

		foreach ($sections as $section) {
			$sectionIndex++;

			$detailSectionHtml = $section->ownerDocument->saveHTML($section);
			$crawler = new Crawler($detailSectionHtml);
			$textNodes = $crawler->filterXPath('//div[@class="detailSection"]/text()');
			$textNodes->each(function ($node) use (&$finalArr, &$membersNames) {
				$nodeValue = trim($node->text());
				if (!empty($nodeValue)) {
					$membersNames[] = $nodeValue;
				}
			});
		}

		$selected_officer_index = array_search("Officer/Director Detail", $data);
		if ($selected_officer_index === false) {
			$selected_officer_index = array_search("Authorized Person(s) Detail", $data);
		}

		$selected_name_address_index = array_search("Name & Address", $data);
		$selected_index = ($selected_officer_index > 0 && $selected_name_address_index > 0) ? $selected_officer_index + 2 : 0;

		if ($selected_index) {
			for ($j = $selected_index; $j <= count($data); $j) {

				if ($data[$j] === "Annual Reports") {
					break;
				}
				$title = preg_replace('/^Title\s*/', '', $data[$j]);
				$members[$memberIndex]['member_title'] = trim($title);
				$members[$memberIndex]['member_address'] = $data[$j + 1];
				$memberIndex++;

				$j = (($j + 2) > count($data)) ? count($data) : $j + 2;
			}
		}

		if (count($members) > 0 && count($membersNames) > 0 &&  count($membersNames) == count($members)) :
			for ($i = 0; $i < count($members); $i++) {


				$first_name = $membersNames[$i];
				$last_name = '';

				if (strpos($membersNames[$i], ',') !== false) {
					$parts = preg_split('/,\s*/', $membersNames[$i]);
					$first_name = end($parts);
					$last_name = implode(' ', array_slice($parts, 0, -1));
				}
				$full_name = trim($first_name . ' ' . $last_name);
				$members[$i]['member_name'] = $full_name;

				// $members[$i]['member_name'] = $membersNames[$i];

				$dataExists = DB::table('contactscraps')
					->where('c_full_name', $full_name)
					->where('c_title', $members[$i]['member_title'])
					->where('lead_id', $lead_id)
					->exists();

				// If data does not exist, insert it
				if (!$dataExists) {
					DB::table('contactscraps')->insert([
						'c_full_name' => $full_name,
						'c_title' => $members[$i]['member_title'],
						'lead_id' => $lead_id,
						'c_first_name' => $first_name,
						'c_last_name' => $last_name,
						'added_by_scrap_apis' => 1,
					]);
				}
			}
			$finalArr = $members;
		endif;
		return ['status' => 'success', 'message' => 'Scraped Successfully', 'data' => $finalArr];
	}

	function extractInteger($str)
	{
		// Use a regular expression to find the first sequence of digits in the string
		preg_match('/\d+/', $str, $matches);
		// Convert the result to an integer
		return isset($matches[0]) ? intval($matches[0]) : null;
	}

	public function migratecontacts(Request $request)
	{

		$leadIds = $request->contactsId;
		$currentPageLeadId = $request->currentPageLeadId;

		if (count($leadIds) <= 0) :
			return response()->json(['leadsCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;
		if ($currentPageLeadId <= 0) :
			return response()->json(['leadsCount' => 0, 'message' => 'Mandatory Parameter missing.PLease contact administrator.']);
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
		return response()->json(['leadsCount' => count($leadIds), 'message' => 'Migration of contacts done.']);
	}



	public function scrap_sunbiz_contacts($business_name, $business_name_href)
	{
		echo $business_name;
		$response = Http::get(
			'https://search.sunbiz.org' . $business_name_href
		);
		$data = [];
		if ($response->successful()) {
			$crawler = new Crawler($response->body());
			// dd($crawler);
			$crawler->filter(
				'.detailSection'
			)->each(
				function ($node) use (&$data) {
					dd($node->filter(''));
				}
			);
		}
	}


	public function scrap_county(REQUEST $request)
	{
		$url = "https://www.fl-counties.com/about-floridas-counties/florida-cities-by-county/";
		$response = Http::get($url);

		if ($response->successful()) {
			// Create a new Crawler instance
			$crawler = new Crawler($response->body());
			$storeData = [];
			$county = [];
			$storeData = [];
			$countyName = '';
			$crawler->filter('h4')->each(function (Crawler $countyNode) use (&$countyData) {
				$countyName = $countyNode->text();
				$cities = [];
				$countyName = str_replace('COUNTY', '', $countyName);
				$cityNode = $countyNode->nextAll()->filter('p')->first();
				if ($cityNode->count() > 0) {
					// Extract city names separated by <br> tag
					$cities = explode('<br>', $cityNode->html());
				}
				foreach ($cities as $key => $city) {
					$storeData['Search Keyword'] = 'Condominium';
					$storeData['City'] = trim($city);
					$storeData['State'] = 'Florida';
					$storeData['State Code'] = 'FL';
					$storeData['County'] = $countyName;
					$storeData['Business Type'] = 'Condominium';
					$county = ScrapCity::storeCountyAndCity($storeData);
				}
			});
			return 'Successfully stored';
		} else {
			return 'Error occured';
		}
	}


	public function scrap_white_pages(REQUEST $request)
	{
		// $url = "https://www.whitepages.com/name/Jeremy-Lambert/FL?fs=1&searchedName=jeremy%20lambert&searchedLocation=Florida";
		// $url = "https://pbcpao.gov/MasterSearch/SearchResults?propertyType=RE&searchvalue=Weinbaum%20";
		$url = "https://www.smarty.com/products/us-address-verification?street=22%20Degroat%20Rd&secondary=&city=Sandyston&state=NJ&zipcode=07827&address-type=us-street-components";
		echo $url . '-----------------';
		$response = Http::get($url);
		// dd($response);
		$data = [];
		if ($response->successful()) {
			// dd($response);
			// Create a new Crawler instance
			$crawler = new Crawler($response->body());
			dd($crawler);
			$crawler->filter('[id^="searchGrid"]')->filter('tr')->each(
				function ($node) use (&$data) {
					// dd($node->getContent());
					// $node->filter('tr')->each(function ($node1) use (&$data) {
					// dd($node);
					// });
					// dd($node->filter('tr')->filter('td'));
					dd($node->filter('td.propertyDetails'));
					// dd($node->filterXPath('td'));


					// $data[] = $node->filter('td')->siblings();

				}
			);
		}
	}


	//format contact verification data from property appraisers
	public function formatPropAppraisersContactData($data)
	{
		if (count($data)) {
			//check for duplicates

		} else {
			//store data
		}
	}





	// private static function storeCountyAndCity($storeData, $countyName)
	// {
	// if ($countyName) {
	// $scrapCounty = ScrapCounty::updateOrCreate(
	// [
	// 'name' => $countyName,
	// ],
	// [
	// 'name' => $countyName,
	// 'status' => 1,
	// ]
	// );
	// if ($scrapCounty) {
	// $scrapCity = ScrapCity::updateOrCreate([
	// 'search_keyword' => $storeData['search_keyword'],
	// 'city' => $storeData['city'],
	// 'state' => $storeData['state'],
	// 'state_code' => $storeData['state_code'],
	// 'county_id' => $scrapCounty->id
	// ], [
	// 'search_keyword' => $storeData['search_keyword'],
	// 'city' => $storeData['city'],
	// 'state' => $storeData['state'],
	// 'state_code' => $storeData['state_code'],
	// 'county_id' => $scrapCounty->id,
	// 'status' => 1
	// ]);
	// return $scrapCounty->id;
	// }
	// }
	// }

	//Open People search ------------------------------- Start
	public function contactMailingAddressVerification($firstname, $lastname, $state)
	{
		/**
		 * Requires libcurl
		 */
		$auth_token = 'Authorization: Bearer ' . self::callOpenPeopleAuthentication();
		$curl_contact_arr = [];
		$final_contact_arr = [];
		// $all_lead_contacts = Contact::orderBy('id', 'asc')->select('id', 'c_first_name', 'c_last_name', 'c_city', 'c_state', 'c_zip')->limit(1)->get();
		// dd($all_lead_contacts);
		// foreach ($all_lead_contacts as $key => $contact) {
		// echo $lead->name . '=====' . $lead->id;
		$contacts = array();
		$contacts['c_first_name'] = $firstname;
		$contacts['c_last_name'] = $lastname;
		$contacts['c_state'] = $state;
		$contacts['c_city'] = '';
		$call_open_people_search_arr =  self::callOpenPeopleSearch(
			$contacts,
			$auth_token
		);
		array_push(
			$curl_contact_arr,
			$call_open_people_search_arr
		);
		// }
		// dd($curl_contact_arr);
		foreach ($curl_contact_arr[0] as $index => $val) {
			// foreach ($val as $inner_index => $inner_val) {
			if (in_array($val['dataCategoryName'], array('Property', 'Voters'))) {
				array_push(
					$final_contact_arr,
					$val
				);
			}
			// }
		}
		dd($final_contact_arr);
	}
	//Open People search curl
	public function callOpenPeopleSearch($contacts, $auth_token)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.openpeoplesearch.com/api/v1/consumer/NameSearch',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{
                "firstName": "' . $contacts['c_first_name'] . '",
                "middleName": "",
                "lastName": "' . $contacts['c_last_name'] . '",
                "state": "' . $contacts['c_state'] . '",
                "city":"' . $contacts['c_city'] . '"
            }',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				$auth_token
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);
		return $response['results'];
	}
	//call OpenPeople Authentication
	public function callOpenPeopleAuthentication()
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.openpeoplesearch.com/api/v1/User/authenticate',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{
                "username": "jeremy@fhinsure.com",
                "password": "89Insurance89@"
            }',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);
		return $response['token'];
	}
	//Open People search ------------------------------- End

	//People DataLabs ------------------------------- Start
	public function contactFromPeopleDataLabs($firstname, $lastname, $state)
	{
		$curl_contact_arr = [];
		$final_contact_arr = [];

		$call_open_people_search_arr =  self::callPeopleDataLabsSearch($firstname, $lastname, $state);
		array_push(
			$curl_contact_arr,
			$call_open_people_search_arr
		);
		dd($curl_contact_arr);
	}
	//People DataLabs search curl
	public function callPeopleDataLabsSearch($firstname, $lastname, $state)
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.peopledatalabs.com/v5/person/enrich?first_name=' . $firstname . '&last_name=' . $lastname . '&region=' . $state);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'X-Api-Key: 80afaac673fc3cf890deb4694645d7caf598eed909f3f5e5db8bad445182d7bd',
		]);

		$response = curl_exec($ch);

		curl_close($ch);
		$response = json_decode($response, true);
		// return $response;
		if ($response['status'] == 200)
			return $response['data'];
		else
			return $response['error']['message'];
	}
}
