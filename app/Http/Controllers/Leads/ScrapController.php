<?php

// namespace App\Http\Controllers;

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ContactScrap;
use App\Model\LeadsModel\Lead;
use App\Model\ScrapCity;
use App\Traits\CommonFunctionsTrait;
use DB;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Symfony\Component\DomCrawler\Crawler;

class ScrapController extends Controller
{
    use CommonFunctionsTrait;

    public function scrap($searchTerms = 'condo', $geoLocationTerms = 'Jacksonville', $stateCode = 'FL')
    {
        $total = 0;
        $each = 0;
        $paginateTotal = 0;
        $paginate = 1;
        $arr = [];
        $data = [];
        $crawlerStr = '';
        do {
            $crawlerStr = 'https://www.yellowpages.com/search?search_terms='.$searchTerms.'&geo_location_terms='.$geoLocationTerms.'%2C%20'.$stateCode;

            if ($paginate > 1) {
                $crawlerStr .= '&page='.$paginate;
            }
            $response = Http::get($crawlerStr);
            array_push($arr, $crawlerStr);
            // Check if request was successful
            if ($response->successful()) {
                // Create a new Crawler instance
                $crawler = new Crawler($response->body());

                if ($paginateTotal == 0) {
                    $paginationStr = $crawler->filter('[class^="showing-count"]')->text();
                    $pieces = explode(' ', $paginationStr);

                    $total = (int) $pieces[3]; // 30
                    $each = (int) explode('-', $pieces[1])[1];

                    $paginateTotal = (int) ceil($total / $each);
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
        } while ($paginate <= $paginateTotal);

        return Lead::evaluateCrawlerLeads($data);
    }

    public function scrapSunbizGetLeads(Request $request)
    {
        $vars = [];

        return view('scrap.sunbiz_index', compact($vars));
    }

    public function updateSingleBusinessName(Request $request)
    {
        $leadDetail = Lead::find($request->lead_id);
        $leadId = $leadDetail->id;

        $leadSlug = '';
        if ($leadDetail->name !== $request->lead_name) {
            $leadSlug = $this->generateSlug([$leadDetail->type, $request->lead_name, $leadDetail->city, $leadDetail->zip]);

            $request->lead_name = $this->removeSpecialCharacters($request->lead_name);

            if ($leadSlug) { // based on slug allow addition of business in db
                $slugExistance = $this->checkLeadSlugExistanceWithDistance($leadSlug, $leadDetail->latitude, $leadDetail->longitude);
                $leadDetail->lead_slug = $leadSlug;
                if (is_array($slugExistance) && isset($slugExistance['existanceCount']) && $slugExistance['existanceCount'] > 0) {
                    return ['status' => 'error', 'message' => implode('</br>', $slugExistance['message'])];
                }
            }
        }

        $updateLead = Lead::where('id', $request->lead_id)->update(['name' => $request->lead_name, 'lead_slug' => $leadSlug]);
        if ($updateLead) {
            return ['status' => 'success', 'message' => 'Business name updated successfully.'];
        }

        return ['status' => 'error', 'message' => 'An error occured.Please contact administrator.'];
    }

    public function getScrapSunbizGetLeadsApi_original(Request $request)
    {
        $isAdminUser = auth()->user()->can('agent-create');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw = $request->input('draw', 1);
        $filterOnColumnNumber = $request->input('order')[0]['column'];
        $filterOnColumnName = $request->input('columns')[$filterOnColumnNumber]['data'] ?? 'id';
        $orderBy = $request->input('order')[0]['dir'] ?? 'desc';
        $currentUrl = URL::to('/');
        $searchValue = $request->input('search')['value'] ?? null;

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

        $pendingBusinessesQuery->with('contactscraps')
            ->orderBy($filterOnColumnName, $orderBy)
            ->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url');

        $filteredRecords = $pendingBusinessesQuery->count();

        return datatables()->eloquent($pendingBusinessesQuery)
            ->addIndexColumn()
            ->addColumn('name', function ($business) use ($currentUrl) {
                return isset($business->name)
                    ? '<span id="businessName_'.$business->id.'"><a href="'.$currentUrl.'/leads/edit/'.$business->id.'" target="_blank">'.$business->name.'</a></span>  <a href="javascript:void(0)" class="edit_business_lead editbtn'.$business->id.'" data-id="'.$business->id.'" ><i class="fas fa-pen"></i></a>'
                    : '';
            })
            ->addColumn('list_url', function ($business) {
                return isset($business->sunbiz_list_url)
                    ? '<a href="'.$business['sunbiz_list_url'].'" target="_blank">Listing Url</a>'
                    : '';
            })
            ->addColumn('details_url', function ($business) {
                return isset($business->sunbiz_details_url) && ! empty($business->sunbiz_details_url)
                    ? '<a href="'.$business['sunbiz_details_url'].'" target="_blank">Details Url</a>'
                    : '';
            })
            ->addColumn('contacts', function ($business) {
                return view('scrap.partials.members', ['members' => $business->contactscraps, 'lead_id' => $business->id])->render();
            })
            ->addColumn('scrap', function ($business) {
                return view('scrap.partials.actions', ['id' => $business->id, 'list_url' => $business->list_url, 'lead_id' => $business->id])->render();
            })
            ->addColumn('actions', function ($business) {
                return '<a href="/scrap/compare/'.$business->id.'" >Assign</a>';
            })
            ->rawColumns(['name', 'list_url', 'details_url', 'contacts', 'scrap', 'fetch_contacts', 'actions'])
            ->with('recordsTotal', $totalRecords)
            ->make(true);
    }

    public function getScrapSunbizGetLeadsApi(Request $request)
    {
        $isAdminUser = auth()->user()->can('agent-create');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw = $request->input('draw', 1);
        $filterOnColumnNumber = $request->input('order')[0]['column'];
        $filterOnColumnName = $request->input('columns')[$filterOnColumnNumber]['data'] ?? 'id';
        $orderBy = $request->input('order')[0]['dir'] ?? 'desc';
        $currentUrl = URL::to('/');
        $searchValue = $request->input('search')['value'] ?? null;

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
        if (! empty($searchValue)) {
            $pendingBusinessesQuery->where('name', 'like', '%'.$searchValue.'%');
        }

        $pendingBusinessesQuery->with('contactscraps')
            ->orderBy($filterOnColumnName, $orderBy)
            ->select('id', 'name', 'sunbiz_status', 'sunbiz_list_url', 'sunbiz_details_url');

        // Get the count of filtered records
        $filteredRecords = $pendingBusinessesQuery->count();

        return datatables()->eloquent($pendingBusinessesQuery)
            ->addIndexColumn()
            ->addColumn('name', function ($business) use ($currentUrl) {
                return isset($business->name)
                    ? '<span id="businessName_'.$business->id.'"><a href="'.$currentUrl.'/leads/edit/'.$business->id.'" target="_blank">'.$business->name.'</a></span>  <a href="javascript:void(0)" class="edit_business_lead editbtn'.$business->id.'" data-id="'.$business->id.'" ><i class="fas fa-pen"></i></a>'
                    : '';
            })
            ->addColumn('list_url', function ($business) {
                return isset($business->sunbiz_list_url)
                    ? '<a href="'.$business['sunbiz_list_url'].'" target="_blank">Listing Url</a>'
                    : '';
            })
            ->addColumn('details_url', function ($business) {
                return isset($business->sunbiz_details_url) && ! empty($business->sunbiz_details_url)
                    ? '<a href="'.$business['sunbiz_details_url'].'" target="_blank">Details Url</a>'
                    : '';
            })
            ->addColumn('contacts', function ($business) {
                return view('scrap.partials.members', ['members' => $business->contactscraps, 'lead_id' => $business->id])->render();
            })
            ->addColumn('scrap', function ($business) {
                return view('scrap.partials.actions', ['id' => $business->id, 'list_url' => $business->list_url, 'lead_id' => $business->id])->render();
            })
            ->addColumn('actions', function ($business) {
                return '<a href="/scrap/compare/'.$business->id.'">Assign</a> / <a href="javascript:void(0)" data-id="'.$business->id.'" class="delete_button" >Delete<a/>';
            })
            ->rawColumns(['name', 'list_url', 'details_url', 'contacts', 'scrap', 'actions'])
            ->filter(function ($query) {
                if ($keyword = request()->input('search.value')) {
                    return $query->where('name', 'like', '%'.$keyword.'%');
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
            if (empty($request->selectedValues)) {
                return response()->json(['status' => false, 'message' => 'Please select Business Name']);
            }

            if (is_array($request->selectedValues)) {
                $selectedValues = $request->selectedValues;
            } else {
                $selectedValues = explode(',', $request->selectedValues);
            }

            // echo "<pre>";print_r($selectedValues);exit;

            Lead::where('is_added_by_bot', 1)
                ->where(function ($query) {
                    $query->where('sunbiz_status', 'crawled')
                        ->orWhere('sunbiz_status', 'failedcrawl');
                })
                ->whereIn('id', $selectedValues)
                ->delete();

            DB::table('leads_files')->whereIn('lead_id', $selectedValues)->delete();

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
        if ($contacts->count() <= 0 && $tempContacts->count() <= 0) {
            toastr()->error('No contacts exists for respective lead.');

            return back();
        }
        $contactsArr = [$contacts, $tempContacts];

        return view('scrap.assign_contacts', compact('contacts', 'tempContacts'));
    }

    public function calculateSimilarity($leadName, $businessName, $entityNameProbabilityArr)
    {
        $similarity = 0;
        if (strpos($businessName, $leadName) !== false) {
            $similarity += 0.5;
        }
        foreach ($entityNameProbabilityArr as $entity) {
            if (strpos($businessName, $entity) !== false) {
                $similarity += 0.1;
            }
        }

        return $similarity;
    }

    public function getDataBySunbizUrl(Request $request)
    {
        $url = $request->details_url;
        $leadId = $request->lead_id;
        if (empty($url) && $leadId <= 0) {
            return ['status' => 'error', 'message' => 'Parameters mismatch. Please contact administrator.'];
        }
        $client = new Client;
        $crawler = $client->request('GET', $url);

        if (! $crawler) {
            return ['status' => 'error', 'message' => 'Nothing crawled from the provided link.'];
        }

        $finalArr = [];

        $membersNames = $data = $members = [];
        $memberIndex = $sectionIndex = 0;

        $sections = $crawler->filter('.detailSection');

        $crawler->filter('span')->each(function (Crawler $spanNode) use (&$finalArr, &$data) {
            $text = $spanNode->text();
            $data[] = $text;
            if ($text === 'Principal Address') {
                $finalArr['principal_address'] = $spanNode->nextAll()->text();
            } elseif ($text === 'Mailing Address') {
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
                if (! empty($nodeValue)) {
                    $membersNames[] = $nodeValue;
                }
            });
        }

        $selectedOfficerIndex = array_search('Officer/Director Detail', $data);
        if ($selectedOfficerIndex === false) {
            $selectedOfficerIndex = array_search('Authorized Person(s) Detail', $data);
        }

        $selectedNameAddressIndex = array_search('Name & Address', $data);
        $selectedIndex = ($selectedOfficerIndex > 0 && $selectedNameAddressIndex > 0) ? $selectedOfficerIndex + 2 : 0;

        if ($selectedIndex) {
            for ($j = $selectedIndex; $j <= count($data); $j) {

                if ($data[$j] === 'Annual Reports') {
                    break;
                }
                $title = preg_replace('/^Title\s*/', '', $data[$j]);
                $members[$memberIndex]['member_title'] = trim($title);
                $members[$memberIndex]['member_address'] = $data[$j + 1];
                $memberIndex++;

                $j = (($j + 2) > count($data)) ? count($data) : $j + 2;
            }
        }

        if (count($members) > 0 && count($membersNames) > 0 && count($membersNames) == count($members)) {
            for ($i = 0; $i < count($members); $i++) {

                $firstName = $membersNames[$i];
                $lastName = '';

                if (strpos($membersNames[$i], ',') !== false) {
                    $parts = preg_split('/,\s*/', $membersNames[$i]);
                    $firstName = end($parts);
                    $lastName = implode(' ', array_slice($parts, 0, -1));
                }
                $fullName = trim($firstName.' '.$lastName);
                $members[$i]['member_name'] = $fullName;

                $dataExists = DB::table('contactscraps')
                    ->where('c_full_name', $fullName)
                    ->where('c_title', $members[$i]['member_title'])
                    ->where('lead_id', $leadId)
                    ->exists();

                // If data does not exist, insert it
                if (! $dataExists) {
                    DB::table('contactscraps')->insert([
                        'c_full_name' => $fullName,
                        'c_title' => $members[$i]['member_title'],
                        'lead_id' => $leadId,
                        'c_first_name' => $firstName,
                        'c_last_name' => $lastName,
                        'added_by_scrap_apis' => 1,
                    ]);
                }
            }
            $finalArr = $members;
        }

        return ['status' => 'success', 'message' => 'Scraped Successfully', 'data' => $finalArr];
    }

    public function extractInteger($str)
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

        if (count($leadIds) <= 0) {
            return response()->json(['leadsCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
        }
        if ($currentPageLeadId <= 0) {
            return response()->json(['leadsCount' => 0, 'message' => 'Mandatory Parameter missing.PLease contact administrator.']);
        }

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
                        'prospect_verified' => 'pending',
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
                        'prospect_verified' => 'pending',
                    ]);

                    array_push($insertedOrUpdatedIds, $contact->id);
                }

                // You can use the $lastInsertedOrUpdatedId as needed

                ContactScrap::where('id', $temps->id)->delete();
            }

            if (count($insertedOrUpdatedIds) > 0 && count($intArray) > 0 && $currentPageLeadId > 0) {
                Contact::whereNotIn('id', $insertedOrUpdatedIds)->where('lead_id', $currentPageLeadId)->delete();
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

    public function scrap_sunbiz_contacts($businessName, $businessNameHref)
    {
        $response = Http::get(
            'https://search.sunbiz.org'.$businessNameHref
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
        $url = 'https://www.fl-counties.com/about-floridas-counties/florida-cities-by-county/';
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
        $url = 'https://www.smarty.com/products/us-address-verification?street=22%20Degroat%20Rd&secondary=&city=Sandyston&state=NJ&zipcode=07827&address-type=us-street-components';
        $response = Http::get($url);
        $data = [];
        if ($response->successful()) {
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

    // Open People search ------------------------------- Start
    public function contactMailingAddressVerification($firstname, $lastname, $state)
    {
        /**
         * Requires libcurl
         */
        $authToken = 'Authorization: Bearer '.self::callOpenPeopleAuthentication();
        $curlContactArr = [];
        $finalContactArr = [];
        $contacts = [];
        $contacts['c_first_name'] = $firstname;
        $contacts['c_last_name'] = $lastname;
        $contacts['c_state'] = $state;
        $contacts['c_city'] = '';
        $callOpenPeopleSearchArr = self::callOpenPeopleSearch(
            $contacts,
            $authToken
        );
        array_push(
            $curlContactArr,
            $callOpenPeopleSearchArr
        );
        foreach ($curlContactArr[0] as $val) {
            if (in_array($val['dataCategoryName'], ['Property', 'Voters'])) {
                array_push(
                    $finalContactArr,
                    $val
                );
            }
        }
    }

    // Open People search curl
    public function callOpenPeopleSearch($contacts, $authToken)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openpeoplesearch.com/api/v1/consumer/NameSearch',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "firstName": "'.$contacts['c_first_name'].'",
                "middleName": "",
                "lastName": "'.$contacts['c_last_name'].'",
                "state": "'.$contacts['c_state'].'",
                "city":"'.$contacts['c_city'].'"
            }',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                $authToken,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        return $response['results'];
    }

    // call OpenPeople Authentication
    public function callOpenPeopleAuthentication()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
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
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        return $response['token'];
    }
    // Open People search ------------------------------- End

    // People DataLabs ------------------------------- Start
    public function contactFromPeopleDataLabs($firstname, $lastname, $state)
    {
        $curlContactArr = [];
        $finalContactArr = [];

        $callOpenPeopleSearchArr = self::callPeopleDataLabsSearch($firstname, $lastname, $state);
        array_push(
            $curlContactArr,
            $callOpenPeopleSearchArr
        );
    }

    // People DataLabs search curl
    public function callPeopleDataLabsSearch($firstname, $lastname, $state)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.peopledatalabs.com/v5/person/enrich?first_name='.$firstname.'&last_name='.$lastname.'&region='.$state);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: 80afaac673fc3cf890deb4694645d7caf598eed909f3f5e5db8bad445182d7bd',
        ]);

        $response = curl_exec($ch);

        curl_close($ch);
        $response = json_decode($response, true);
        if ($response['status'] == 200) {
            return $response['data'];
        } else {
            return $response['error']['message'];
        }
    }
}
