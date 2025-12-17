<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Log;
use App\Model\LeadsModel\Note;
use App\Model\User;
use App\Model\File;
use App\Model\Campaign;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use Illuminate\Support\Facades\Storage;
use DataTables;
use Redirect, Response;
use Validator;
use DB;
use Illuminate\Support\Facades\Schema;
use Session;
// use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel;
use App\Exports\LeadsExport;
use App\Model\County;
use App\Model\LeadsModel\Filter;
use Carbon\Carbon;
use App\Traits\CommonFunctionsTrait;
use App\Model\SmtpConfiguration;
use App\Traits\SMTPRelatedTrait;
use App\Jobs\CreateCampaignJob;
use App\Model\Carrier;
use App\Model\Rating;
use App\Model\InsuranceType;
use App\Model\LeadSource;
use App\Model\LeadAsanaDetail;
use App\Model\LeadInfoLog;
use App\Model\LeadAdditionalPolicy;
use Illuminate\Support\Facades\Cache;


use Illuminate\Support\Facades\Http;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

use App\Services\GetSunBizDetailsBasic;

class LeadController extends Controller
{
	use CommonFunctionsTrait,SMTPRelatedTrait;
	private $leadsFiltered;
	/**
	 * Display a listing of the resource.
	 * @return \Illuminate\Http\Response
	 */
	public function getcontactDetails(Request $r)
	{

		echo '<form name="test" method="GET" action="">

					<input type="text" name="lead_id" value="'.$r->lead_id.'"  placeholder="Enter Lead ID"/>
					<input type="submit" value="Get Info" />
				</form>
			';

		if($r->input('lead_id')){

			// echo $r->lead_id;exit;

			$lead = Lead::find($r->lead_id);

			if(!$lead){
				return redirect("/testpurpose_url_test");
			}



			// $url = "https://search.sunbiz.org/Inquiry/CorporationSearch/SearchResultDetail?inquirytype=EntityName&directionType=Initial&searchNameOrder=LAKECLARKEGARDENSCONDOMINIUM%207111940&aggregateId=domnp-711194-199fd3d0-7ec7-4fb3-b8ab-587205e1518f&searchTerm=LAKE%20CLAR";
			// $list_url= "https://search.sunbiz.org/Inquiry/CorporationSearch/SearchResults/EntityName/LAKE CLARKE GARDENS CONDOMINIUM INC/Page1?searchNameOrder=LAKECLARKEGARDENSCONDOMINIUMINC";
			// $lead_id = 1;

			$url = $lead->sunbiz_details_url;
			$list_url= $lead->sunbiz_list_url;
			$lead_id = $lead->id;
			// $url = 'https://search.sunbiz.org' . $url;
			$client = new Client();
			$crawler = $client->request('GET', $url);

			if (!$crawler) {
				return [];
			}

			$finalArr = [
				'list_url' => $list_url,
				'details_url' => $url,
				'principal_address' => null,
				'mailing_address' => null,
				'registered_name' => null,
				'registered_address' => null,
				'members' => []
			];

			$membersNames = $data = $members = [];
			$memberIndex = $sectionIndex = 0;

			$sections = $crawler->filter('.detailSection');

			$spans = $crawler->filter('div.detailSection > span');

			for ($i = 0; $i < $spans->count(); $i++) {
			    $spanNode = $spans->eq($i);
			    $text = trim($spanNode->text());
			    $data[] = $text;

			    if ($text === "Principal Address") {
			        $finalArr['principal_address'] = trim($spans->eq($i + 1)->text());
			    } elseif ($text === "Mailing Address") {
			        $finalArr['mailing_address'] = trim($spans->eq($i + 1)->text());
			    } elseif ($text === "Registered Agent Name & Address") {
			        // Registered name is next span
			        $finalArr['registered_name'] = trim($spans->eq($i + 1)->text());

			        // Registered address is inside the next span's <div>
			        $addressSpan = $spans->eq($i + 2);
			        $addressDiv = $addressSpan->filter('div');

			        if ($addressDiv->count() > 0) {
			            // Collect address lines separated by <br>
			            $rawHtml = $addressDiv->html();
			            $addressLines = preg_split('/<br[^>]*>/i', $rawHtml);
			            $addressLines = array_map(function ($line) {
			                return trim(strip_tags($line));
			            }, $addressLines);
			            $addressLines = array_filter($addressLines); // Remove empty lines
			            $finalArr['registered_address'] = implode(' ', $addressLines);
			        } else {
			            $finalArr['registered_address'] = ''; // fallback
			        }
			    }
			}


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

						if (!isset($data[$j]) || $data[$j] === "Annual Reports") {
							break;
						}
						$title = isset($data[$j]) ? preg_replace('/^Title\s*/', '', $data[$j]) : "";
						$members[$memberIndex]['member_title'] = $title ? trim($title) : "";
						$members[$memberIndex]['member_address'] = isset($data[$j + 1]) ? $data[$j + 1] : "";
					
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


					DB::table('contactscraps')->insert([
						'c_full_name' => $full_name,
						'c_title' => $members[$i]['member_title'],
						'lead_id' => $lead_id,
						'c_first_name' => $first_name,
						'c_last_name' => $last_name,
						'added_by_scrap_apis' => 1,
					]);
				}
				$finalArr['members'] = $members;
			endif;

			if(!empty($finalArr['registered_name']) || !empty($finalArr['registered_address'])){
				Lead::where("id",$lead_id)->update([
					"sunbiz_registered_name" => $finalArr['registered_name'],
					"sunbiz_registered_address" => $finalArr['registered_address'],
				]);
			}
			echo "Fetched details"."<br>";

			echo "<pre>";print_r($finalArr);exit;
			return $finalArr;
		}
	}
	public function getSunbizDetails(Request $r)
	{

		echo '<form name="test" method="GET" action="">

					<input type="text" name="lead_name" value="'.$r->lead_name.'"  placeholder="Enter Lead Name"/>
					<input type="submit" value="Get Info" />
				</form>
			';

		if($r->input('lead_name')){
			$leadName = $r->input('lead_name');

			$getSunBiz = new GetSunBizDetailsBasic();

			$leadName = $getSunBiz->replaceSubstrings($leadName);

			$scrap_response = $getSunBiz->scrap_sunbiz($leadName);

			echo "<pre>";print_r($scrap_response);exit;
		}
	
	}
	function __construct()
	{
		$this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete|lead-file-list|lead-action', ['only' => ['index', 'store']]);
		$this->middleware('permission:lead-create', ['only' => ['create', 'store']]);
		$this->middleware('permission:lead-edit', ['only' => ['edit', 'update']]);
		$this->middleware('permission:lead-delete', ['only' => ['destroy', 'delete_leads', 'remove_leads']]);
		$this->middleware('permission:contact-delete', ['only' => ['destroy', 'delete_leads', 'remove_leads']]);
	}
	public function updatecontactslug(Request $request)
	{
		$emptySlugColl = Contact::whereNull('contact_slug')->orWhere('contact_slug', '')->orderBy('id', 'desc')->limit(1000)->get();
		foreach ($emptySlugColl as $singleEmptyCollection) {
			$address = $singleEmptyCollection->c_address1;

			// Use preg_match to extract the number
			if (preg_match('/\d+/', $address, $matches)) {
				$address = $matches[0];
			}

			echo	$contact_slug = ($singleEmptyCollection->c_email) ? $singleEmptyCollection->c_email : $this->generateSlug([$singleEmptyCollection->c_first_name . '_' . $singleEmptyCollection->c_last_name . '_' . $address]);

			$singleEmptyCollection->id;

			$singleEmptyCollection->contact_slug = $contact_slug;
			$singleEmptyCollection->save();
			// dd($singleEmptyCollection);

			$contactCount = Contact::where('contact_slug', $contact_slug)->count();
			if ($contactCount > 1) {
				echo $contactCount . 'aaaa';
				Contact::where('contact_slug', $contact_slug)->update(['c_merge_status' => 1]);
			}
		}
	}

	public function updateslug(Request $request)
	{
		// echo 'hello';
		// die();
		$emptySlugColl = Lead::whereNull('lead_slug')->orWhere('lead_slug', '')->orderBy('id', 'desc')->limit(1000)->get();

		// dd($emptySlugColl);
		foreach ($emptySlugColl as $singleEmptyCollection) {
			// dd($singleEmptyCollection);
			echo $lead_slug = $this->generateSlug([$singleEmptyCollection->type, $singleEmptyCollection->name, $singleEmptyCollection->city, $singleEmptyCollection->zip]);
			$singleEmptyCollection->lead_slug = $lead_slug;
			$singleEmptyCollection->save();

			$leadCount = Lead::where('lead_slug', $lead_slug)->count();
			if ($leadCount > 1) {
				echo $leadCount . 'aaaa';
				Lead::where('lead_slug', $lead_slug)->update(['merge_status' => 1]);
			}
		}
	}

	public function merge(Request $request, $slug)
	{
		$leads = Lead::where('lead_slug', $slug)->get();
		if ($leads->count() <= 0) :
			toastr()->error("No mergeable businesses exists for above slug.");
			return back();
		endif;
		$compareArr = array();
		$i = 1;
		foreach ($leads as $key => $lead) :
			$columns = $lead->getFillable();
			// dd($columns);
			$leadData = [];
			$attributes = array_diff_key($lead->getAttributes(), array_flip(['latitude', 'longitude', 'is_added_by_bot', 'merge_status', 'is_client', 'queued_at',  'deleted_at', 'agent_id']));
			foreach ($attributes as $key => $value) {

				$leadData[$key] = $value;
			}


			$leadData['contacts'] = $lead->contacts()->orderBy('contact_slug', 'asc')->pluck('contact_slug')->map(function ($slug, $index) {
				return '<a href="/contacts/merge/' . $slug . '" target="_blank" class=" assign_slug">' . $slug . '</a>';
			})->implode(', ');

			// if ($lead->contacts()->count() > 0) {
			// If the lead has contacts, add the "Assign Data" button
			$leadData['contacts'] .= ' <button class="merge_to_current_lead" id="assign_slug_to_lead' . $i++ . '" >Assign Data</button>';
			// }

			$compareArr[] = $leadData;
		endforeach;
		return view('leads.merge_leads', compact('compareArr'));
	}

	public function completemerge(Request $request)
	{
		$payloadData = $request->all();
		// dd($payloadData)
		try {

			$payloadData['merge_status'] = 0;
			unset($payloadData['contacts']);
			unset($payloadData['mergeable_contacts']);



			// Find leads with the specified lead_slug, excluding the lead with the specified id
			Lead::where('lead_slug', $payloadData['lead_slug'])
				->where('id', '!=', $payloadData['id'])
				->update(['merge_status' => 0]);


			Lead::where('lead_slug', $payloadData['lead_slug'])
				->where('id', '!=', $payloadData['id'])
				->delete();

			// Update the lead with the specified id


			Lead::where('id', $payloadData['id'])
				->update($payloadData);

			// dd($payloadData);

			// Return success response
			return response()->json(['status' => true, 'message' => 'Business merged successfully!']);
		} catch (\Exception $e) {
			// Return error response
			return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
		}
	}
	public function moveContacts(Request $request)
	{
		$payloadData = $request->all();
		$mergeLeadIdFrom = $request->mergeLeadIdFrom;
		$mergeLeadIdTo = $request->mergeLeadIdTo;
		try {
			$contactsToUpdate = Contact::where('lead_id', $mergeLeadIdFrom)->get();

			// dd($contactsToUpdate);
			// Update lead_id to 4 for each contact
			foreach ($contactsToUpdate as $contact) {
				$contact->lead_id = $mergeLeadIdTo;
				$contact->save();
			}

			if ($contactsToUpdate->count() > 0) {

				return response()->json(['status' => 1, 'message' => 'Contacts assigned to the selected lead successfully']);
			} else {

				return response()->json(['status' => 0, 'message' => 'No contacts found with lead_id = 2']);
			}
		} catch (\Exception $e) {
			// Return error response
			return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
		}
	}

	public function index(Request $request)
	{
		//dropdown data
		$tableHeadingName = parent::$leadTableHeadingName;

		//get integer/date columns
		$columnsType = Lead::Get_column_type();
		$states = Lead::Lead_States();
		$lead_filters = Filter::GetFilters();
		//get all values from db to use them in filter
		//leads
		$leadsTypes = Cache::rememberForever('lead_types_list', function () {
		    return Lead::select('type')->distinct()->orderBy('type')->pluck('type', 'type')->all();
		});
		$leadsRenMonths = ['' => 'Select Month', 'empty' => 'Is Empty'] + Lead::Lead_Months();
		$leadsStates = ['' => 'Select State', 'empty' => 'Is Empty'] + $states;

		$leadsCounties =  Lead::select('county')
        ->distinct()
        ->orderBy('county', 'asc')
        ->pluck('county', 'county')
        ->all();

		// $leadsinsurrance = Lead::orderBy('ins_prop_carrier', 'asc')->pluck('ins_prop_carrier', 'ins_prop_carrier')->all();

		$property = InsuranceType::where('name', 'Property')->first();

		$leadsinsurrance = optional($property->carriers()
		    ->where('status', 1)
		    ->orderBy('name') // optional sorting
		    ->get()
		)->pluck('name', 'name')->all();
		$leadsinsurrance = ['' => 'Select Insurance Property Carrier'] + $leadsinsurrance;

		//contacts
		// $contactsTitle = Contact::orderBy('c_title', 'asc')->pluck('c_title', 'c_title')->all();
		$contactsTitle = Lead::contactTitle();

		$google_map_api_key = env('GOOGLE_MAP_API_KEY');
		$agents = User::select('users.id','users.name','users.email')->role(['Agent','Service & Agent'])->get();
		// echo "<pre>";print_r($agents);exit;
		$agent_users = [];
		$agent_users[0] = 'Select Agent';
		foreach ($agents as $key => $agent) {
			$agent_users[$agent->id] = $agent->name . '(' . $agent->email . ')';
		}
		$all_account_list_permission = auth()->user()->can('all-accounts-list-pipedrive');

		$leadSource = self::getLeadSource();
		
		// echo "<pre>";print_r($agent_users);exit;
		$vars = array('tableHeadingName', 'columnsType', 'states', 'leadsinsurrance', 'leadsTypes', 'leadsRenMonths', 'leadsStates', 'leadsCounties', 'contactsTitle', 'lead_filters', 'google_map_api_key', 'agent_users','all_account_list_permission','leadSource');
		if ($request->id) {
			$leadId = $request->id;
			// $vars = array('tableHeadingName', 'columnsType', 'states', 'leadsinsurrance', 'leadsTypes', 'leadsRenMonths', 'leadsStates', 'leadsCounties', 'contactsTitle', 'contactsStates', 'contactsCounties', 'leadId');
			array_push($vars, 'leadId');
		}
		return view('leads.index', compact($vars));
	}

	/**------------------------------------------------------------*
	 *                  Lead
	 *------------------------------------------------------------*
	 *
	 * Display datatables
	 */
	public function get_custom_leads(Request $request)
	{
		$start = $request->input('start', 0);
		$length = $request->input('length', 10); // Default length or adjust as needed
		$draw = $request->input('draw', 1);
		$filter_on_column_number = $request->input('order')[0]['column'];
		$filter_on_column_name = $request->input('columns')[$filter_on_column_number]['data'] ?? 'id';
		$order_by = $request->input('order')[0]['dir'] ?? 'desc';
		$search_value = $request->input('search')['value'] ?? null;

		$columnsType = Lead::Get_column_type();
		$filters = (!empty($request->searchFields)) ? $request->searchFields : ('');
		$location_leads_id = (!empty($request->location_leads_id)) ? json_decode($request->location_leads_id) : ('');
		$location_leads_id_search = (!empty($request->location_leads_id_search)) ? $request->location_leads_id_search : false;
		$distance_filter = null;
		$dialing_filters_clicked = $request->dialing_filters_clicked;

		$filter_contact_by = "";
		if(!empty($filters)) {
			$filters_obj = json_decode(json_encode($filters));
			foreach ($filters_obj as $key) {
				foreach($key as $val) {
					if(!empty($val->s_name) && $val->s_name === "added_by_scrap_apis") {
						$filter_contact_by = $val->s_val;
						break;
					}
				}
			}
		}
		
		if ($location_leads_id_search) {
			$table = Lead::with('contacts')->whereIn('id', $location_leads_id);
		} else if ($filters && array_key_exists(0, $filters)) {
			$distance_filter = $filters[0];
			$address_text = !empty($distance_filter[0]['address_text'])?$distance_filter[0]['address_text']:'';
			$distance_op = !empty($distance_filter[0]['distance_op'])?$distance_filter[0]['distance_op']:'';
			$distance = !empty($distance_filter[0]['distance'])?$distance_filter[0]['distance']:'';
			$distance_query_selection_checkbox = !empty($distance_filter[0]['distance_query_selection_checkbox'])?$distance_filter[0]['distance_query_selection_checkbox']:'false';
			$lead_business_names_search = !empty($distance_filter[0]['lead_business_names_search'])?$distance_filter[0]['lead_business_names_search']:'';
			$lead_business_name_search_id = !empty($distance_filter[0]['lead_business_name_search_id'])?$distance_filter[0]['lead_business_name_search_id']:0;
			if ($distance_query_selection_checkbox == 'true' && $lead_business_names_search && $lead_business_name_search_id > 0) {
				$lead = Lead::find($lead_business_name_search_id);
				if ($lead) {
					$address_text = ($lead->address1 && $lead->address2) ? $lead->address1 . ' ' . $lead->address2 : ($lead->address1 ? $lead->address1 : $lead->address2);
				}
			}
			$lat_long = parent::getLatLngFromGoogle($address_text);
			// echo "<pre>";print_r($lat_long);exit;
			if (is_null($lat_long['lat']) && is_null($lat_long['long'])) {
				return array('status' => false, 'message' => 'Please add valid address');
			}
			$latitude = $lat_long['lat'];
			$longitude = $lat_long['long'];
			$filters = array_slice($filters, 1, NULL, true);
			$table = Lead::with('contacts');
			if(!empty($distance_op) && !empty($distance)){
				$table = $table->whereIn('id', function ($query) use ($latitude, $longitude, $distance_op, $distance) {
					$query->from('leads')->selectRaw("id")->whereRaw("SQRT(POW(69.1 * (latitude - $latitude), 2) + POW(69.1 * ($longitude - longitude) * COS(latitude / 57.3), 2)) $distance_op $distance");
					// $query->selectRaw("leads.id")->whereRaw("SQRT(POW(69.1 * (latitude - $latitude), 2) + POW(69.1 * ($longitude - longitude) * COS(latitude / 57.3), 2)) $distance_op $distance");
				});
			}
		} else {
			$table =  Lead::with('contacts');
		}
		$campaignId = (!empty($request->campaign)) ? $request->campaign : ('');
		$is_admin_user = auth()->user()->can('agent-create');
		// filter leads by search filters. FN: Support/helper.php
		$leadsQuery = filter_leads($table, $filters, $columnsType, $campaignId);

		if ($dialing_filters_clicked) : // listing for dialinglist
			$ownedLeads = DB::table('dialings_leads')->where('status', 'owned')->where('owned_by_agent_id', '>', 0)->get()->pluck('lead_id');
			$leadsQuery->where('is_client', 0);
			// if ($ownedLeads) :
			// 	$leadsQuery->whereNotIn('id', $ownedLeads);
			// endif;
			$leads = $distance_filter ? $leadsQuery : $leadsQuery
			// ->select('leads.*');
			->select('leads.id','leads.type','leads.name','leads.creation_date','leads.address1','leads.city','leads.state','leads.zip','leads.county','leads.unit_count','leads.renewal_month','leads.latitude','leads.longitude','leads.lead_slug','leads.is_added_by_bot','leads.merge_status','leads.sunbiz_registered_name','leads.sunbiz_registered_address',"leads.pipeline_agent_id")
			->with('ownedAgent:id,name');
			$dialingContactStatus = self::getDialingStatusOptions();

			$leadsQuery->whereHas('contacts', function ($query) use ($dialingContactStatus) {
				$query->where('c_phone', '<>', '');
				$query->whereIn('c_status', $dialingContactStatus);
			});
			$totalRecords = $leadsQuery->count();
			$filteredRecords = $leadsQuery->count();

			if (!empty($search_value)) {
				$leadsQuery = $this->get_search_data($search_value, $leadsQuery);
				$filteredRecords = $leadsQuery->count();
			}

			$leads = $leadsQuery->orderBy('id', 'desc')->offset($start)->limit($length);

		else : // normal listing
			$totalRecords = $leadsQuery->count();
			if (!empty($search_value)) {
				$leadsQuery = $this->get_search_data($search_value, $leadsQuery);
				$filteredRecords = $leadsQuery->count();
			}
			else{
				$filteredRecords = $leadsQuery->count();
			}

			$leads = $distance_filter ? $leadsQuery : $leadsQuery
			// ->select('leads.*');
			->select('leads.id','leads.type','leads.name','leads.creation_date','leads.address1','leads.city','leads.state','leads.zip','leads.county','leads.unit_count','leads.renewal_month','leads.latitude','leads.longitude','leads.lead_slug','leads.is_added_by_bot','leads.merge_status','leads.sunbiz_registered_name','leads.sunbiz_registered_address',"leads.pipeline_agent_id")
			// ->leftJoin('users', 'users.id', '=', 'leads.pipeline_agent_id');
			->with('ownedAgent:id,name');
			// $leads = $leads->orderBy('id', 'desc')->offset($start)->limit($length);
			$leads = $leads->orderBy($filter_on_column_name, $order_by)->offset($start)->limit($length);
		// ->get();

		endif;

		return datatables()->of($leads)
			->addColumn('owned_agent_name', function ($lead) {
		        return $lead->ownedAgent->name ?? '';
		    })
			->addIndexColumn()
			->editColumn('creation_date', function ($leadsQuery) {
				if (isset($leadsQuery->creation_date)) {
					//Fix for removing default date(01/01/1970) that Yajra adds to table when doesn't find any date in db
					return date('m/d/Y', strtotime($leadsQuery->creation_date));
				}
			})
			->editColumn('renewal_date', function ($leadsQuery) {
				if (isset($leadsQuery->renewal_date)) {
					//Fix for removing default date(01/01/1970) that Yajra adds to table when doesn't find any date in db
					return date('m/d/Y', strtotime($leadsQuery->renewal_date));
				}
			})
			->addColumn('contacts', function (Lead $lead) use ($filter_contact_by) {
				return view('leads.partials.contact-phone', compact('lead', 'filter_contact_by'));
			})
			->addColumn('action', function ($row) use ($is_admin_user) {
				$editLead      = 'lead-edit';
				$deleteLead    = 'lead-delete';
				$crudRoutePart = 'lead';
				return view('leads.partials.lead-buttons-actions', compact('editLead', 'deleteLead', 'crudRoutePart', 'row', 'is_admin_user'));
			})
			->rawColumns(['action'])
			->setTotalRecords($totalRecords)
			->setFilteredRecords($filteredRecords)
			->make(true);
	}

	public function get_search_data($search_value, $leadsQuery)
	{

		$columns_to_search = ['leads.type', 'leads.name', 'leads.creation_date', 'leads.city','leads.address1', 'leads.state', 'leads.zip', 'leads.county', 'leads.unit_count', 'leads.latitude', 'leads.longitude', 'leads.renewal_month', 'leads.lead_slug','leads.sunbiz_registered_name','leads.sunbiz_registered_address'];
		return $leadsQuery = $this->search($leadsQuery, $search_value, $columns_to_search);
		// return $leadsQuery->where(function ($leadsQuery) use ($search_value) {
		// 	$leadsQuery->where('type', 'like', '%' . $search_value . '%')
		// 		->orWhere('name', 'like', '%' . $search_value . '%')
		// 		->orWhere('city', 'like', '%' . $search_value . '%')
		// 		->orWhere('state', 'like', '%' . $search_value . '%')
		// 		->orWhere('zip', 'like', '%' . $search_value . '%')
		// 		->orWhere('county', 'like', '%' . $search_value . '%')
		// 		->orWhere('unit_count', 'like', '%' . $search_value . '%')
		// 		->orWhere('latitude', 'like', '%' . $search_value . '%')
		// 		->orWhere('longitude', 'like', '%' . $search_value . '%')
		// 		->orWhere('renewal_month', 'like', '%' . $search_value . '%')
		// 		->orWhere('lead_slug', 'like', '%' . $search_value . '%');
		// });
	}

	public function zipCodeLeads(Request $request)
	{
		$zips = DB::table('leads')->select('zip')->distinct()->get();
		if ($zips->count() > 0) {
			$geocoded = array();
			$API_KEY = env('GOOGLE_MAP_API_KEY');
			foreach ($zips as $zip_code) {
				if ($zip_code->zip) {
					$zip = $zip_code->zip;
					$serviceUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=$zip&sensor=false&key=$API_KEY";
					$result_string = file_get_contents($serviceUrl);
					$result = json_decode($result_string, true);
					if (array_key_exists('status', $result) && $result['status'] == 'OK') {
						$location = $result['results'][0]['geometry']['location'];
						$geocoded[$zip] = $location;
					} else {
						$geocoded[$zip] = $zip . ' not found';
					}
				}
			}
			return $geocoded;
		}
	}





	/**
	 * Display the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $encrpt_id)
	{   //find the lead based on id
		$id = base64_decode($encrpt_id);
		$lead = Lead::find($id);
		if (!$lead) {

			toastr()->error('This Lead doesn\'t exist');
			return redirect('/leads');
		}
		//get lead campaigns and paginate


		$data  = $lead->campaigns()->select('id','name','status','campaign_date')->toBase()
		->orderBy('id', 'DESC')->paginate(10, ['*'], 'campaignsShow');
		// echo "<pre>";print_r($data);exit;
		$actions = $lead->actions()->orderBy('id', 'DESC')->paginate(10, ['*'], 'actionsShow');
		// dd($actions);
		if (!$lead) {

			toastr()->error('This Lead doesn\'t exist');
			return redirect('/leads');
		}
		// find lead logs
		$logs = $lead->logs->sortByDesc('id');
		// $notes = Lead::find($id)->notes->sortByDesc('id');

		$notes = DB::table('notes')
			->leftJoin('contacts', 'notes.contact_id', '=', 'contacts.id')
			->select('notes.*', 'contacts.c_full_name as contact_name')
			->where('notes.lead_id', $id)
			->where('notes.deleted_at', null)
			->orderBy('notes.id', 'desc')->get();
		// echo "<pre>";print_r($notes);exit;

		//find the contacts assigned to this lead
		$contacts = $lead->contacts;

		$contactsFullNames = []; // empty array
		$contactsFullNames[''] = "Select Contact";
		foreach ($contacts as $ct) {
			$contactsFullNames[$ct->id] = $ct->c_first_name . ' ' . $ct->c_last_name; // add contact full names and id's to array , to use it in blade
		}
		$contactsFullNames['other'] = "Other"; //add other to array


		//get lead campaigns
		$campaigns = $lead->campaigns->sortByDesc('id');
		$states = Lead::Lead_States();
		//get the counties
		$counties = Lead::Lead_Counties();
		// get the months;
		$months = Lead::Lead_Months();
		// get the roofcovering;
		$roofcovering = Lead::Lead_Roof_Covering();
		// get the roof_geometry;
		$roof_geometry = Lead::Lead_Roof_Geometry();

		// get the year;
		$years = parent::getYearDropdown();

		$statusOptions = [
			'Bad Number', 'Call Back', 'Do Not Call', 'No Answer (Left Message)', 'Not Interested', 'Select Status', 'AOR Received', 'Policies Received'
		];

		// $property = InsuranceType::where('name', 'Property')->first();
	    // if ($property) {
	    //     $carriersWithProperty = $property->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithProperty = collect();
	    // }
	    // unset($generalLiability);

		// $generalLiability = InsuranceType::where('name', 'General Liability')->first();
	    // if ($generalLiability) {
	    //     $carriersWithGeneralLiability = $generalLiability->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithGeneralLiability = collect();
	    // }
	    // unset($generalLiability);

	    // $crimeInsurance = InsuranceType::where('name', 'Crime Insurance')->first();
	    // if ($crimeInsurance) {
	    //     $carriersWithCrimeInsurance = $crimeInsurance->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithCrimeInsurance = collect();
	    // }
	    // unset($crimeInsurance);

	    // $directorOfficor = InsuranceType::where('name', 'Directors & Officers')->first();
	    // if ($directorOfficor) {
	    //     $carriersWithDirectorOfficor = $directorOfficor->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithDirectorOfficor = collect();
	    // }
	    // unset($directorOfficor);

	    // $unbrella = InsuranceType::where('name', 'Umbrella')->first();
	    // if ($unbrella) {
	    //     $carriersWithUnbrella = $unbrella->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithUnbrella = collect();
	    // }
	    // unset($unbrella);

	    // $workCompensation = InsuranceType::where('name', 'Workers Compensation')->first();
	    // if ($workCompensation) {
	    //     $carriersWithWorkCompensation = $workCompensation->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithWorkCompensation = collect();
	    // }
	    // unset($workCompensation);

	    // $flood = InsuranceType::where('name', 'Flood')->first();
	    // if ($flood) {
	    //     $carriersWithFlood = $flood->carriers()->where('status', 1)->get();
	    // } else {
	    //     $carriersWithFlood = collect();
	    // }
	    // unset($flood);

	    $renewed_lead = 0;
	    $previous_lead = [];
	    $previous_lead_date_list = [];
	    $previous_lead_policy_list = [];

	    $leadAsanaDetail = LeadAsanaDetail::where('lead_id',$id)->select('renewed_lead')->first();
	    if($leadAsanaDetail){
	    	$renewed_lead = $leadAsanaDetail->renewed_lead;

	    	$leadInfoLog = LeadInfoLog::where('lead_id',$id)->where('table_name','Lead')->select('data')->orderBy('renewal_date','DESC')->first();
	    	if($leadInfoLog){
	    		$previous_lead = $this->rawRefreshLeadData(json_decode($leadInfoLog->data));
	    	}
	    	unset($leadInfoLog);

	    	$leadpolicyLog = LeadInfoLog::where('lead_id',$id)->where('table_name','LeadAdditionalPolicy')->select('data')->orderBy('renewal_date','DESC')->first();
	    	if($leadpolicyLog){
	    		$previous_lead_policy_list = $this->rawRefreshAdditionalLeadData(json_decode($leadpolicyLog->data));
	    	}
	    	unset($leadpolicyLog);

	    	$previous_lead_date_list = LeadInfoLog::where('lead_id',$id)->where('table_name','Lead')->select('renewal_date')->orderBy('renewal_date','DESC')->get();
	    }
	    unset($leadAsanaDetail);

	    $additonalPolicy = $lead->leadAdditionalpolicy()->get();


		return view('leads.show', compact('lead', 'statusOptions', 'contacts', 'logs', 'contactsFullNames', 'notes', 'data', 'actions', 'campaigns','states','counties','months','years','roof_geometry','roofcovering','additonalPolicy','renewed_lead','previous_lead','previous_lead_date_list',
			'previous_lead_policy_list'));
	}

	/**
	 * Show the form for editing the specified resource.
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($encrpt_id)
	{
		//find the lead based on id
		$id = base64_decode($encrpt_id);
		$lead = Lead::find($id);
		if($lead){
			if(!empty($lead->premium)){
				$lead->premium = intval($lead->premium);
			}
			if(!empty($lead->insured_amount)){
				$lead->insured_amount = intval($lead->insured_amount);
			}
		}
		// echo "<pre>";print_r(auth()->user()->roles[0]->name);exit;

		// echo "<pre>";
		// print_r($lead->leadStatus->name);
		// exit;
		if (!$lead) {

			toastr()->error('This Lead doesn\'t exist');
			return redirect('/leads');
		}

		$data  = $lead->campaigns()->select('id','name','status','campaign_date')->toBase()
		->orderBy('id', 'DESC')->paginate(10, ['*'], 'campaignsShow');
		$actions = $lead->actions()->orderBy('id', 'DESC')->paginate(10, ['*'], 'actionsShow');


		//get all lead ins_prop_carrier from leads db table
		// $leadsinsurrance = Lead::orderBy('ins_prop_carrier','asc')->pluck('ins_prop_carrier','ins_prop_carrier')->all();
		// $leadsinsurrance = array(
		// 	'' => 'Select Insurance Property Carrier',
		// 	'American Coastal' => 'American Coastal',
		// 	'Heritage' => 'Heritage',
		// 	'SRU / Lloyds of London' => 'SRU / Lloyds of London',
		// 	'QBE' => 'QBE',
		// 	'Catalytic' => 'Catalytic',
		// 	'Arrowhead' => 'Arrowhead',
		// 	'Layered Program/Multiple Carriers' => 'Layered Program/Multiple Carriers',
		// 	'Centauri' => 'Centauri',
		// 	'Avatar' => 'Avatar',
		// 	'IAT/Occidental' => 'IAT/Occidental',
		// 	'Frontline' => 'Frontline',
		// 	'Ventus' => 'Ventus',
		// 	'Velocity Risk Underwriters' => 'Velocity Risk Underwriters',
		// 	'Lloyds Of London/Other' => 'Lloyds Of London/Other',
		// 	'Citizens' => 'Citizens',
		// 	'other' => 'Other'
		// );


		$logs = $lead->logs->sortByDesc('id'); 
		// $notes = Lead::find($id)->notes->sortByDesc('id');
		$notes = DB::table('notes')
	    ->leftJoin('contacts', 'notes.contact_id', '=', 'contacts.id')
	    ->select('notes.id','notes.description','notes.created_at', 'contacts.c_full_name as contact_name')
	    ->where('notes.lead_id', $id)
	    ->whereNull('notes.deleted_at')
	    ->orderByDesc('notes.id')
	    ->get();

		// echo "<pre>";print_r($notes);exit;

		//find the contacts assigned to this lead
		// $contacts = Lead::find($id)->contacts;
		//get all contact titles from contacts db table
		$contactsTitle = Lead::contactTitle();
		//,'other'=>'Other'

		//get the states
		$states = Lead::Lead_States();
		//get the counties
		$counties = Lead::Lead_Counties();
		// get the months;
		$months = Lead::Lead_Months();
		// get the roofcovering;
		$roofcovering = Lead::Lead_Roof_Covering();
		// get the roof_geometry;
		$roof_geometry = Lead::Lead_Roof_Geometry();

		// get the year;
		$years = parent::getYearDropdown();

		//find the contacts assigned to this lead
		$contacts = $lead->contacts()->where(function ($query) {
			// $query->where('c_status', '<>', 'Bad Number')
			// 	->orWhere('c_status', '<>', 'Do not Call')
			// 	->orWhere('c_status', '<br>', 'Not Interested');
		})
		->with('contactStatus:id,name')
		->get();
		// dd($contacts); 
		$statusOptions = self::getContactStatusOptions();

		$agentlist = parent::getagentListBasedonLogin();

		// echo "<pre>";print_r($agentlist);exit;


		$contactsFullNames = []; // empty array
		$contactsFullNames[''] = "Select Contact";
		foreach ($contacts as $ct) {

			$contactsFullNames[$ct->id] = $ct->c_first_name . ' ' . $ct->c_last_name; // add contact full names and id's to array , to use it in blade
		}
		$contactsFullNames['other'] = "Other"; //add other to array

		//get lead campaigns
		$campaigns = $lead->campaigns->sortByDesc('id');

		//get current insurance

		$smtp_data = $this->checkMailConfiguration();

		$insuranceTypesToFetch = [
		    'Property' => ['carrierVar' => 'carriersWithProperty', 'ratingVar' => 'ratingsWithProperty'],
		    'General Liability' => ['carrierVar' => 'carriersWithGeneralLiability', 'ratingVar' => 'ratingsWithGeneralLiability'],
		    'Crime Insurance' => ['carrierVar' => 'carriersWithCrimeInsurance', 'ratingVar' => 'ratingsWithCrimeInsurance'],
		    'Directors & Officers' => ['carrierVar' => 'carriersWithDirectorOfficor', 'ratingVar' => 'ratingsWithDirectorOfficor'],
		    'Umbrella' => ['carrierVar' => 'carriersWithUnbrella', 'ratingVar' => 'ratingsWithUnbrella'],
		    'Workers Compensation' => ['carrierVar' => 'carriersWithWorkCompensation', 'ratingVar' => 'ratingsWithWorkCompensation'],
		    'Flood' => ['carrierVar' => 'carriersWithFlood', 'ratingVar' => 'ratingsWithFlood'],
		];

		foreach ($insuranceTypesToFetch as $typeName => $vars) {
		    $type = InsuranceType::where('name', $typeName)->first();
		    if ($type) {
		        ${$vars['carrierVar']} = $type->carriers()->select('carriers.id','carriers.name')->where('status', 1)->get();
		        ${$vars['ratingVar']} = $type->ratings()->select('ratings.id','ratings.name')->where('status', 1)->get();
		    } else {
		        ${$vars['carrierVar']} = collect();
		        ${$vars['ratingVar']} = collect();
		    }
		    unset($type);
		}

		$onlyCarriers = [
		    'Difference In Conditions' => 'carriersWithDC',
		    'X-Wind' => 'carriersWithXW',
		    'Equipment Breakdown' => 'carriersWithEB',
		    'Commercial AutoMobile' => 'carriersWithCA',
		    'Marina' => 'carriersWithMarina',
		];

		foreach ($onlyCarriers as $typeName => $varName) {
		    $type = InsuranceType::where('name', $typeName)->first();
		    if ($type) {
		        ${$varName} = $type->carriers()->select('carriers.id','carriers.name')->where('status', 1)->get();
		    } else {
		        ${$varName} = collect();
		    }
		    unset($type);
		}

	    $leadSource = LeadSource::select('id','name')->where('status', 1)->get();

	    $renewed_lead = 0;
	    $previous_lead = [];
	    $previous_lead_date_list = [];
	    $previous_lead_policy_list = [];

	    $leadAsanaDetail = LeadAsanaDetail::where('lead_id',$id)->select('renewed_lead')->first();
	    if($leadAsanaDetail){
	    	$renewed_lead = $leadAsanaDetail->renewed_lead;

	    	$leadInfoLog = LeadInfoLog::where('lead_id',$id)->where('table_name','Lead')->select('data')->orderBy('renewal_date','DESC')->first();
	    	if($leadInfoLog){
	    		$previous_lead = $this->rawRefreshLeadData(json_decode($leadInfoLog->data));
	    	}
	    	unset($leadInfoLog);

	    	$leadpolicyLog = LeadInfoLog::where('lead_id',$id)->where('table_name','LeadAdditionalPolicy')->select('data')->orderBy('renewal_date','DESC')->first();
	    	if($leadpolicyLog){
	    		$previous_lead_policy_list = $this->rawRefreshAdditionalLeadData(json_decode($leadpolicyLog->data));
	    	}
	    	unset($leadpolicyLog);

	    	$previous_lead_date_list = LeadInfoLog::where('lead_id',$id)->where('table_name','Lead')->select('renewal_date')->orderBy('renewal_date','DESC')->get();
	    }
	    unset($leadAsanaDetail);

	    $additonalPolicy = $lead->leadAdditionalpolicy()->get();

	    $additionalPoliciesCarrier = $this->additionalPoliciesCarrier;

	    // echo "<pre>";print_r($carriersWithCA);exit;

		return view('leads.edit', compact('lead', 'contacts', 'statusOptions', 'contactsTitle', 'states', 'counties', 'months', 'years', 'logs', 'notes', 'data', 'contactsFullNames', 'actions', 'campaigns', 'roof_geometry', 'roofcovering', 'smtp_data','agentlist','carriersWithGeneralLiability','carriersWithCrimeInsurance','carriersWithDirectorOfficor','carriersWithUnbrella','carriersWithWorkCompensation','carriersWithFlood','carriersWithProperty','ratingsWithProperty','ratingsWithGeneralLiability','ratingsWithCrimeInsurance','ratingsWithDirectorOfficor','ratingsWithUnbrella','ratingsWithWorkCompensation','ratingsWithFlood','leadSource','renewed_lead','previous_lead','previous_lead_date_list','additonalPolicy','previous_lead_policy_list','carriersWithDC','carriersWithXW','carriersWithEB','carriersWithCA','carriersWithMarina','additionalPoliciesCarrier'));
	}

	public function carrierList(Request $request)
	{
		$insurance = InsuranceType::where('name', $request->name)->first();
	    if ($insurance) {
	        $carriers = $insurance->carriers()->select('carriers.id','carriers.name')->where('status', 1)->get();
	    } else {
	        $carriers = collect();
	    }
	    unset($insurance);

	    return response()->json([
            'status' => true,
            'carriers' => $carriers
        ]);
	}

	public function rawRefreshLeadData($previous_lead)
	{
		//carrier
		if(!empty($previous_lead->ins_prop_carrier)){
			$previous_lead->ins_prop_carrier = Carrier::where("id", $previous_lead->ins_prop_carrier)->value('name');
		}
		if(!empty($previous_lead->general_liability)){
			$previous_lead->general_liability = Carrier::where("id", $previous_lead->general_liability)->value('name');
		}
		if(!empty($previous_lead->crime_insurance)){
			$previous_lead->crime_insurance = Carrier::where("id", $previous_lead->crime_insurance)->value('name');
		}
		if(!empty($previous_lead->directors_officers)){
			$previous_lead->directors_officers = Carrier::where("id", $previous_lead->directors_officers)->value('name');
		}
		if(!empty($previous_lead->umbrella)){
			$previous_lead->umbrella = Carrier::where("id", $previous_lead->umbrella)->value('name');
		}
		if(!empty($previous_lead->workers_compensation)){
			$previous_lead->workers_compensation = Carrier::where("id", $previous_lead->workers_compensation)->value('name');
		}
		if(!empty($previous_lead->flood)){
			$previous_lead->flood = Carrier::where("id", $previous_lead->flood)->value('name');
		}
		if(!empty($previous_lead->difference_in_condition)){
			$previous_lead->difference_in_condition = Carrier::where("id", $previous_lead->difference_in_condition)->value('name');
		}
		if(!empty($previous_lead->x_wind)){
			$previous_lead->x_wind = Carrier::where("id", $previous_lead->x_wind)->value('name');
		}
		if(!empty($previous_lead->equipment_breakdown)){
			$previous_lead->equipment_breakdown = Carrier::where("id", $previous_lead->equipment_breakdown)->value('name');
		}
		if(!empty($previous_lead->commercial_automobiles)){
			$previous_lead->commercial_automobiles = Carrier::where("id", $previous_lead->commercial_automobiles)->value('name');
		}
		if(!empty($previous_lead->marina)){
			$previous_lead->marina = Carrier::where("id", $previous_lead->marina)->value('name');
		}

		//rating
		if(!empty($previous_lead->rating)){
			$previous_lead->rating = Rating::where("id", $previous_lead->rating)->value('name');
		}
		if(!empty($previous_lead->gl_rating)){
			$previous_lead->gl_rating = Rating::where("id", $previous_lead->gl_rating)->value('name');
		}
		if(!empty($previous_lead->ci_rating)){
			$previous_lead->ci_rating = Rating::where("id", $previous_lead->ci_rating)->value('name');
		}
		if(!empty($previous_lead->do_rating)){
			$previous_lead->do_rating = Rating::where("id", $previous_lead->do_rating)->value('name');
		}
		if(!empty($previous_lead->umbrella_rating)){
			$previous_lead->umbrella_rating = Rating::where("id", $previous_lead->umbrella_rating)->value('name');
		}
		if(!empty($previous_lead->wc_rating)){
			$previous_lead->wc_rating = Rating::where("id", $previous_lead->wc_rating)->value('name');
		}
		if(!empty($previous_lead->flood_rating)){
			$previous_lead->flood_rating = Rating::where("id", $previous_lead->flood_rating)->value('name');
		}

		return $previous_lead;
	}

	public function rawRefreshAdditionalLeadData($previous_lead_policy_list)
	{
		foreach ($previous_lead_policy_list as $keyAddPolicy) {
			if(!empty($keyAddPolicy->carrier)){
				$keyAddPolicy->carrier = Carrier::where("id", $keyAddPolicy->carrier)->value('name');
			}
		}

		return $previous_lead_policy_list;
	}

	public function fetchDateWiseOlderData(Request $request)
	{
		$previous_lead = [];
		$previous_lead_policy_list = [];
		$lead_found = 0;
		$leadInfoLog = LeadInfoLog::where('lead_id',$request->lead_id)->where('table_name','Lead')->where('renewal_date',$request->renewal_date)->select('data')->first();
    	if($leadInfoLog){
    		$lead_found = 1;
    		$previous_lead = $this->rawRefreshLeadData(json_decode($leadInfoLog->data));
    	}
    	unset($leadInfoLog);

    	$leadpolicyLog = LeadInfoLog::where('lead_id',$request->lead_id)->where('table_name','LeadAdditionalPolicy')->select('data')->where('renewal_date',$request->renewal_date)->first();
    	if($leadpolicyLog){
    		$previous_lead_policy_list = $this->rawRefreshAdditionalLeadData(json_decode($leadpolicyLog->data));
    		
    	}
    	unset($leadpolicyLog);

    	return response()->json([
            'status' => true,
            'previous_lead' => $previous_lead,
            'previous_lead_policy_list' => $previous_lead_policy_list,
            'lead_found' => $lead_found,
        ]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//get the states
		$states = Lead::Lead_States();
		//get the counties
		$counties = Lead::Lead_Counties();
		// get the months;
		$months = Lead::Lead_Months();
		// get the roofcovering;
		$roofcovering = Lead::Lead_Roof_Covering();
		// get the roof_geometry;
		$roof_geometry = Lead::Lead_Roof_Geometry();
		// get the year;
		$years = parent::getYearDropdown();

		//get all lead ins_prop_carrier from leads db table
		// $leadsinsurrance = Lead::orderBy('ins_prop_carrier','asc')->pluck('ins_prop_carrier','ins_prop_carrier')->all();
		// $leadsinsurrance['']='Select Insurance Property Carrier';
		$leadsinsurrance = array(
			'' => 'Select Insurance Property Carrier',
			'American Coastal' => 'American Coastal',
			'Heritage' => 'Heritage',
			'SRU / Lloyds of London' => 'SRU / Lloyds of London',
			'QBE' => 'QBE',
			'Catalytic' => 'Catalytic',
			'Arrowhead' => 'Arrowhead',
			'Layered Program/Multiple Carriers' => 'Layered Program/Multiple Carriers',
			'Centauri' => 'Centauri',
			'Avatar' => 'Avatar',
			'IAT/Occidental' => 'IAT/Occidental',
			'Frontline' => 'Frontline',
			'Ventus' => 'Ventus',
			'Velocity Risk Underwriters' => 'Velocity Risk Underwriters',
			'Lloyds Of London/Other' => 'Lloyds Of London/Other',
			'Citizens' => 'Citizens',
			'other' => 'Other'
		);

		$leadSource = LeadSource::select('id','name')->where('status', 1)->get();
		return view('leads.create', compact('leadsinsurrance', 'states', 'counties', 'months', 'years', 'roofcovering', 'roof_geometry','leadSource'));
	}



	/**
	 * Store a newly created resource in storage.
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'type' => 'required|string|max:191',
			'name' => 'required|string|max:191', //|unique:leads,name
			'creation_date' => 'nullable|string|max:191|date_format:Y-m-d',
			'address1' => 'required|string|max:191|regex:/^\d.*/',
			'address2' => 'nullable|string|max:191',
			'city' => 'required|string|max:191',
			'state' => 'nullable|string|max:191',
			'zip' => 'required|string',
			'county' => 'nullable|string|max:191',
			'unit_count' => 'nullable|max:9999|integer',
			// 'renewal_date' => 'nullable|date_format:Y-m-d',
			// 'renewal_month' => 'nullable',
			'premium' => 'nullable|numeric|required_with:premium_year',
			'premium_year' => 'nullable|numeric|required_with:premium',
			'insured_amount' => 'nullable|numeric|required_with:insured_year',
			'insured_year' => 'nullable|numeric|required_with:insured_amount',
			'manag_company' => 'nullable|string|max:191',
			'prop_manager' => 'nullable|string|max:191',
			'current_agency' => 'nullable|string|max:191',
			'current_agent' => 'nullable|string|max:191',
			'ins_prop_carrier' => 'nullable|string|max:191',
			'renewal_carrier_month' => 'nullable|string|max:191',
			'ins_flood' => 'nullable|string|max:191',
			'prop_floor' => 'nullable|numeric|max:191',
			'roof_geom' => 'nullable|string|max:191',
			'roof_covering' => 'nullable|string|max:191'
		];
		$niceNames = [
			'type' => 'Business Type',
			'name' => 'Business Name',
			'address1' => 'Business Address 1',
			'address2' => 'Business Adress 2',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'county' => 'Business County',
			'unit_count' => 'Business Unit Count',
			'renewal_date' => 'Property Insurance Renewal Date',
			'renewal_month' => 'Property Insurance Renewal Month',
			'premium' => 'Business Premium',
			'premium_year' => 'Business Premium Year',
			'insured_amount' => 'Business Insured Amount',
			'insured_year' => 'Business Insured Year',
			'manag_company' => 'Management Company',
			'prop_manager' => 'Property Manager',
			'current_agency' => 'Current Agency',
			'current_agent' => 'Current Agent',
			'ins_prop_carrier' => 'Insurance_Property_Carrier',
			'renewal_carrier_month' => 'Insurance Property Carrier Renewal Month',
			'ins_flood' => 'Insurance_Flood',
			'prop_floor' => 'Property Floors',
			'roof_geom' => 'Roof Geometry',
			'roof_covering' => 'Roof Covering'
		];


		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}


		$input = $request->all();

		//When the county is selected, find the data in the county table and get the ID and costal / noncostal value.
		if ($input['county'] != '') {
			$county = County::where('name', 'like', '%' . $input['county'] . '%')->first();
			// $county = County::where('name', $input['county'])->first();
			if($county){
				$input['county_id'] = $county->id;
				// $input['coastal'] = $county->coastal;
			}
		}
		// $input['longitude'] = NUll;
		$input['latitude'] = NUll;
		$input['longitude'] = NUll;
		if ($input['address1'] || $input['address2']) {
			$new_address = $input['address1'] && $input['address2'] ? $input['address1'] . ' ' . $input['address2'] : ($input['address1'] ? $input['address1'] : $input['address2']);
			$new_address = $input['city'] ? $new_address . ' ' . $input['city'] . ',' :  $new_address;
			$new_address = $input['state'] ? $new_address . ' ' . $input['state'] . ',' :  $new_address;
			$new_address = $input['zip'] ? $new_address . ' ' . $input['zip'] . ',' :  $new_address;
			$lat_long = parent::getLatLngFromGoogle($new_address);
			if (!is_null($lat_long['lat']) && !is_null($lat_long['long'])) {
				$input['latitude'] = $lat_long['lat'];
				$input['longitude'] = $lat_long['long'];
			}
		}


		// $lead_slug = '';
		$lead_slug = $this->generateSlug([$input['type'], $input['name'], $input['city'], $input['zip']]);
		$input['name'] = $this->removeSpecialCharacters($input['name']);

		if ($lead_slug) { // based on slug allow addition of business in db

			$slugExistance = $this->checkLeadSlugExistanceWithDistance($lead_slug, $input['latitude'], $input['longitude']);
			$input['lead_slug'] = $lead_slug;
			if (is_array($slugExistance) && isset($slugExistance["existanceCount"]) && $slugExistance['existanceCount'] > 0) {
				toastr()->error(implode('</br>', $slugExistance['message']));
				return back()->withErrors($validator)->withInput();
			}
			$input['lead_slug'] = $lead_slug;
		}


		//create the new lead
		$lead = Lead::create($input);
		$id = $lead->id;
		$id = base64_encode($id);
		//create log
		create_log($lead, 'Create Lead', '');

		toastr()->success('Lead <b>' . $lead->name . '</b> created successfully');
		return redirect()->route('leads.update', compact('id'));
	}
	/**
	 * Update the specified resource in storage.
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		// echo "<pre>";print_r($request->input());exit;
		$rules = [
			'type' => 'required|string|max:191',
			'name' => 'required|string|max:191' . $id, // |unique:leads,name,
			'creation_date' => 'nullable|string|max:191|date_format:Y-m-d',
			'address1' => 'required|string|max:191|regex:/^\d.*/',
			'address2' => 'nullable|string|max:191',
			'city' => 'required|string|max:191',
			'state' => 'nullable|string|max:191',
			'zip' => 'required|string',
			'county' => 'nullable|string|max:191',
			'unit_count' => 'nullable|max:9999|integer',
			'renewal_date' => 'nullable|date_format:Y-m-d',
			'renewal_month' => 'nullable',
			'premium' => 'nullable|numeric|required_with:premium_year',
			'premium_year' => 'nullable|numeric|required_with:premium',
			'insured_amount' => 'nullable|numeric|required_with:insured_year',
			'insured_year' => 'nullable|numeric|required_with:insured_amount',
			'manag_company' => 'nullable|string|max:191',
			'prop_manager' => 'nullable|string|max:191',
			'current_agency' => 'nullable|string|max:191',
			'current_agent' => 'nullable|string|max:191',
			'ins_flood' => 'nullable|string|max:191',
			'prop_floor' => 'nullable|numeric|max:191',
			'roof_geom' => 'nullable|string|max:191',
			'roof_covering' => 'nullable|string|max:191',

			'ins_prop_carrier' => 'nullable|string|max:191|required_with:renewal_carrier_month',
			'renewal_carrier_month' => 'nullable|required_with:ins_prop_carrier',

			'general_liability' => 'nullable|string|max:191|required_with:GL_ren_month',
			'GL_ren_month' => 'nullable|string|max:191|required_with:general_liability',
			'gl_expiry_premium' => 'nullable|numeric|required_with:gl_policy_renewal_date',
			'gl_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:gl_expiry_premium',

			'crime_insurance' => 'nullable|string|max:191|required_with:CI_ren_month',
			'CI_ren_month' => 'nullable|string|max:191|required_with:crime_insurance',
			'ci_expiry_premium' => 'nullable|numeric|required_with:ci_policy_renewal_date',
			'ci_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:ci_expiry_premium',

			'directors_officers' => 'nullable|string|max:191|required_with:DO_ren_month',
			'DO_ren_month' => 'nullable|string|max:191|required_with:directors_officers',
			'do_expiry_premium' => 'nullable|numeric|required_with:do_policy_renewal_date',
			'do_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:do_expiry_premium',

			'workers_compensation' => 'nullable|string|max:191|required_with:WC_ren_month',
			'WC_ren_month' => 'nullable|string|max:191|required_with:workers_compensation',
			'wc_expiry_premium' => 'nullable|numeric|required_with:wc_policy_renewal_date',
			'wc_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:wc_expiry_premium',

			'umbrella' => 'nullable|string|max:191|required_with:U_ren_month',
			'U_ren_month' => 'nullable|string|max:191|required_with:umbrella',
			'umbrella_expiry_premium' => 'nullable|numeric|required_with:umbrella_policy_renewal_date',
			'umbrella_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:umbrella_expiry_premium',

			'flood' => 'nullable|string|max:191|required_with:F_ren_month',
    		'F_ren_month' => 'nullable|string|max:191|required_with:flood',
			'flood_expiry_premium' => 'nullable|numeric|required_with:flood_policy_renewal_date',
			'flood_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:flood_expiry_premium',

			'difference_in_condition' => 'nullable|string|max:191|required_with:dic_ren_month',
    		'dic_ren_month' => 'nullable|string|max:191|required_with:difference_in_condition',
			'dic_expiry_premium' => 'nullable|numeric|required_with:dic_policy_renewal_date',
			'dic_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:dic_expiry_premium',

			'x_wind' => 'nullable|string|max:191|required_with:xw_ren_month',
    		'xw_ren_month' => 'nullable|string|max:191|required_with:x_wind',
			'xw_expiry_premium' => 'nullable|numeric|required_with:xw_policy_renewal_date',
			'xw_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:xw_expiry_premium',

			'equipment_breakdown' => 'nullable|string|max:191|required_with:eb_ren_month',
    		'eb_ren_month' => 'nullable|string|max:191|required_with:equipment_breakdown',
			'eb_expiry_premium' => 'nullable|numeric|required_with:eb_policy_renewal_date',
			'eb_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:eb_expiry_premium',

			'commercial_automobiles' => 'nullable|string|max:191|required_with:ca_ren_month',
    		'ca_ren_month' => 'nullable|string|max:191|required_with:commercial_automobiles',
			'ca_expiry_premium' => 'nullable|numeric|required_with:ca_policy_renewal_date',
			'ca_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:ca_expiry_premium',

			'marina' => 'nullable|string|max:191|required_with:m_ren_month',
    		'm_ren_month' => 'nullable|string|max:191|required_with:marina',
			'm_expiry_premium' => 'nullable|numeric|required_with:m_policy_renewal_date',
			'm_policy_renewal_date' => 'nullable|date_format:Y-m-d|required_with:m_expiry_premium',
		];
		$niceNames = [
			'type' => 'Business Type',
			'name' => 'Business Name',
			'creation_date' => 'Business Creation Date',
			'address1' => 'Business Address 1',
			'address2' => 'Business Adress 2',
			'city' => 'Business City',
			'state' => 'Business State',
			'zip' => 'Business Zip',
			'county' => 'Business County',
			'unit_count' => 'Business Unit Count',
			'renewal_date' => 'Property Insurance Renewal Date',
			'renewal_month' => 'Property Insurance Renewal Month',
			'premium' => 'Business Premium',
			'premium_year' => 'Business Premium Year',
			'insured_amount' => 'Business Insured Amount',
			'insured_year' => 'Business Insured Year',
			'manag_company' => 'Management Company',
			'prop_manager' => 'Property Manager',
			'current_agency' => 'Current Agency',
			'current_agent' => 'Current Agent',
			'ins_prop_carrier' => 'Insurance_Property_Carrier',
			'renewal_carrier_month' => 'Insurance Property Renewal Career Month',
			'ins_flood' => 'Insurance_Flood',
			'prop_floor' => 'Property Floors',
			'roof_geom' => 'Roof Geometry',
			'roof_covering' => 'Roof Covering',

			'general_liability' => 'General Liability',
			'GL_ren_month' => 'General Liability Renewal Month',
			'gl_expiry_premium' => 'General Liability Expiring Premium',
			'gl_policy_renewal_date' => 'General Liability Policy Renewal Date',

			'crime_insurance' => 'Crime Insurance',
			'CI_ren_month' => 'Crime Insurance Renewal Month',
			'ci_expiry_premium' => 'Crime Insurance Expiring Premium',
			'ci_policy_renewal_date' => 'Crime Insurance Policy Renewal Date',

			'directors_officers' => 'Directors & Officers',
			'DO_ren_month' => 'Directors & Officers Renewal Month',
			'do_expiry_premium' => 'Directors & Officers Expiring Premium',
			'do_policy_renewal_date' => 'Directors & Officers Policy Renewal Date',

			'workers_compensation' => 'Workers Compensation',
			'WC_ren_month' => 'Workers Compensation Renewal Month',
			'wc_expiry_premium' => 'Workers Compensation Expiring Premium',
			'wc_policy_renewal_date' => 'Workers Compensation Policy Renewal Date',

			'umbrella' => 'Umbrella',
			'U_ren_month' => 'Umbrella Renewal Month',
			'umbrella_expiry_premium' => 'Umbrella Expiring Premium',
			'umbrella_policy_renewal_date' => 'Umbrella Policy Renewal Date',

			'flood' => 'Flood',
			'F_ren_month' => 'Flood Renewal Month',
			'flood_expiry_premium' => 'Flood Expiring Premium',
			'flood_policy_renewal_date' => 'Flood Policy Renewal Date',

			'difference_in_condition' => 'Difference In Conditions',
			'dic_ren_month' => 'Difference In Conditions Renewal Month',
			'dic_expiry_premium' => 'Difference In Conditions Expiring Premium',
			'dic_policy_renewal_date' => 'Difference In Conditions Policy Renewal Date',

			'x_wind' => 'X-Wind',
			'xw_ren_month' => 'X-Wind Renewal Month',
			'xw_expiry_premium' => 'X-Wind Expiring Premium',
			'xw_policy_renewal_date' => 'X-Wind Policy Renewal Date',

			'equipment_breakdown' => 'Equipment Breakdown',
			'eb_ren_month' => 'Equipment Breakdown Renewal Month',
			'eb_expiry_premium' => 'Equipment Breakdown Expiring Premium',
			'eb_policy_renewal_date' => 'Equipment Breakdown Policy Renewal Date',

			'commercial_automobiles' => 'Commercial AutoMobiles',
			'ca_ren_month' => 'Commercial AutoMobiles Renewal Month',
			'ca_expiry_premium' => 'Commercial AutoMobiles Expiring Premium',
			'ca_policy_renewal_date' => 'Commercial AutoMobiles Policy Renewal Date',

			'marina' => 'Marina',
			'm_ren_month' => 'Marina Renewal Month',
			'm_expiry_premium' => 'Marina Expiring Premium',
			'm_policy_renewal_date' => 'Marina Policy Renewal Date',
		];
		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errorMessages = $validator->errors()->all();
			toastr()->error(implode('<br>', $errorMessages));
			return back()->withErrors($validator)->withInput();
		}

		$input = $request->all();

		$policyIds = $request->input('policy_id', []);
	    $carriers = $request->input('carrier', []);
	    $policyTypes = $request->input('policy_type', []);
	    $expiryPremiums = $request->input('a_expiry_premium', []);
	    $policyRenewalDates = $request->input('a_policy_renewal_date', []);
	    $hurricaneDeductibles = $request->input('a_hurricane_deductible', []);
	    $allOtherPerils = $request->input('a_all_other_perils', []);
	    $insuranceCoverage = $request->input('insurance_coverage', []);

	    foreach ($policyIds as $index => $policyId) {
	    	$carrierOtherKey = "carrier{$index}-other";
	        $carrier = ($carriers[$index] === "other" && $request->has($carrierOtherKey) && !empty($request->$carrierOtherKey))
	                    ? $request->$carrierOtherKey
	                    : ($carriers[$index] ?? null);

	        if (!empty($carrier) && !empty($policyTypes[$index])) {
	        }
	        else{
	        	if(empty($carrier)){
	        		toastr()->error("Additional Policy ".($index + 1)." 'Carrier' have not a value. Please provide the required information.");
		    		return back()->withInput();
	        	}
	        	if(empty($policyTypes[$index])){
	        		toastr()->error("Additional Policy ".($index + 1)." 'Policy Type' have not a value. Please provide the required information.");
		    		return back()->withInput();
	        	}
	        	// if(empty($expiryPremiums[$index])){
	        	// 	toastr()->error("Additional Policy ".($index + 1)." 'Expiring Premium' have not a value. Please provide the required information.");
		    	// 	return back()->withInput();
	        	// }
	        	// if(empty($policyRenewalDates[$index])){
	        	// 	toastr()->error("Additional Policy ".($index + 1)." 'Policy Renewal Date' have not a value. Please provide the required information.");
		    	// 	return back()->withInput();
	        	// }
	        }
	    }
		// carrier
		if (!empty($input['ins_prop_carrier']) && $input['ins_prop_carrier'] == 'other' && empty($input['ins_prop_carrier-other'])) {
		    toastr()->error("You have selected 'Other' for the Property carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['general_liability']) && $input['general_liability'] == 'other' && empty($input['general_liability-other'])) {
		    toastr()->error("You have selected 'Other' for the General Liability carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['crime_insurance']) && $input['crime_insurance'] == 'other' && empty($input['crime_insurance-other'])) {
		    toastr()->error("You have selected 'Other' for the Crime Insurance carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['directors_officers']) && $input['directors_officers'] == 'other' && empty($input['directors_officers-other'])) {
		    toastr()->error("You have selected 'Other' for the Directors & Officers carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['umbrella_exclusions']) && $input['umbrella_exclusions'] == 'other' && empty($input['umbrella_exclusions-other'])) {
		    toastr()->error("You have selected 'Other' for the Umbrella carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['workers_compensation']) && $input['workers_compensation'] == 'other' && empty($input['workers_compensation-other'])) {
		    toastr()->error("You have selected 'Other' for the Workers Compensation carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['flood']) && $input['flood'] == 'other' && empty($input['flood-other'])) {
		    toastr()->error("You have selected 'Other' for the Flood carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['difference_in_condition']) && $input['difference_in_condition'] == 'other' && empty($input['difference_in_condition-other'])) {
		    toastr()->error("You have selected 'Other' for the Difference In Conditions carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['x_wind']) && $input['x_wind'] == 'other' && empty($input['x_wind-other'])) {
		    toastr()->error("You have selected 'Other' for the X-Wind carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['equipment_breakdown']) && $input['equipment_breakdown'] == 'other' && empty($input['equipment_breakdown-other'])) {
		    toastr()->error("You have selected 'Other' for the Equipment Breakdown carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['commercial_automobiles']) && $input['commercial_automobiles'] == 'other' && empty($input['commercial_automobiles-other'])) {
		    toastr()->error("You have selected 'Other' for the Commercial AutoMobiles carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['marina']) && $input['marina'] == 'other' && empty($input['marina-other'])) {
		    toastr()->error("You have selected 'Other' for the Marina carrier but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}

		// ratings
		if (!empty($input['rating']) && $input['rating'] == 'other' && empty($input['rating-other'])) {
		    toastr()->error("You have selected 'Other' for the Property Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['gl_rating']) && $input['gl_rating'] == 'other' && empty($input['gl_rating-other'])) {
		    toastr()->error("You have selected 'Other' for the General Liability Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['ci_rating']) && $input['ci_rating'] == 'other' && empty($input['ci_rating-other'])) {
		    toastr()->error("You have selected 'Other' for the Crime Insurance Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['do_rating']) && $input['do_rating'] == 'other' && empty($input['do_rating-other'])) {
		    toastr()->error("You have selected 'Other' for the Directors & Officers Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['umbrella_rating']) && $input['umbrella_rating'] == 'other' && empty($input['umbrella_rating-other'])) {
		    toastr()->error("You have selected 'Other' for the Umbrella Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['wc_rating']) && $input['wc_rating'] == 'other' && empty($input['wc_rating-other'])) {
		    toastr()->error("You have selected 'Other' for the Workers Compensation Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		if (!empty($input['flood_rating']) && $input['flood_rating'] == 'other' && empty($input['flood_rating-other'])) {
		    toastr()->error("You have selected 'Other' for the Flood Rating but have not provided a value. Please provide the required information.");
		    return back()->withInput();
		}
		//ordinance_of_law
		if (!empty($input['ordinance_of_law']) && $input['ordinance_of_law'] == 'other' && (!isset($input['ordinance_of_law-other']) ||  $input['ordinance_of_law-other'] < 0)) {
		    toastr()->error("You have selected 'Ordinance of Law' other value but have not provided a value in other box. Please provide the required information.");
		    return back()->withInput();
		}
		// echo "<pre>";
		// print_r($input);
		// exit;

		$lead_slug = $this->generateSlug([$input['type'], $input['name'], $input['city'], $input['zip']]);
		$input['name'] = $this->removeSpecialCharacters($input['name']);

		$input['longitude'] = NUll;
		$input['latitude'] = NUll;
		$input['longitude'] = NUll;
		if ($input['address1'] || $input['address2']) {
			$new_address = $input['address1'] && $input['address2'] ? $input['address1'] . ' ' . $input['address2'] : ($input['address1'] ? $input['address1'] : $input['address2']);
			$new_address = $input['city'] ? $new_address . ' ' . $input['city'] . ',' :  $new_address;
			$new_address = $input['state'] ? $new_address . ' ' . $input['state'] . ',' :  $new_address;
			$new_address = $input['zip'] ? $new_address . ' ' . $input['zip'] . ',' :  $new_address;
			$lat_long = parent::getLatLngFromGoogle($new_address);
			if (!is_null($lat_long['lat']) && !is_null($lat_long['long'])) {
				$input['latitude'] = $lat_long['lat'];
				$input['longitude'] = $lat_long['long'];
			}
		}

		if ($lead_slug) { // based on slug allow addition of business in db

			$slugExistance = $this->checkLeadSlugExistanceWithDistance($lead_slug, $input['latitude'], $input['longitude'], $id);
			$input['lead_slug'] = $lead_slug;
			if (is_array($slugExistance) && isset($slugExistance["existanceCount"]) && $slugExistance['existanceCount'] > 0) {
				toastr()->error(implode('</br>', $slugExistance['message']));
				return back()->withErrors($validator)->withInput();
			}
		}

		$input['lead_slug'] = $lead_slug;

		//When the county is selected, find the data in the county table and get the ID and costal / noncostal value.
		$input['county_id'] = null;
		// $input['coastal'] = null;
		if ($input['county'] != '') {
			$county = County::where('name', 'like', '%' . $input['county'] . '%')->first();
			if($county){
				$input['county_id'] = $county->id;
				// $input['coastal'] = $county->coastal;
			}
		} 
		//find the lead to update
		$lead = Lead::find($id);
		if (!$lead) {
			toastr()->error('Something went wrong');
			return back();
		}

		if ($input['address1'] || $input['address2']) {
			$new_address = $input['address1'] && $input['address2'] ? $input['address1'] . ' ' . $input['address2'] : ($input['address1'] ? $input['address1'] : $input['address2']);
			$new_address = $input['city'] ? $new_address . ' ' . $input['city'] . ',' :  $new_address;
			$new_address = $input['state'] ? $new_address . ' ' . $input['state'] . ',' :  $new_address;
			$new_address = $input['zip'] ? $new_address . ' ' . $input['zip'] . ',' :  $new_address;
			$lat_long = parent::getLatLngFromGoogle($new_address);
			if (!is_null($lat_long['lat']) && !is_null($lat_long['long'])) {
				$input['latitude'] = $lat_long['lat'];
				$input['longitude'] = $lat_long['long'];
			}
		}
		// $input['is_client'] = $request->c_is_client ? true : false;
		$input['is_client'] = $request->is_client ? true : false;

		$input['umbrella_exclusions'] = $request->input('umbrella_exclusions') ? implode(',', $request->input('umbrella_exclusions')) : '';
		// carrier
		if(!empty($input['ins_prop_carrier']) && $input['ins_prop_carrier'] == 'other'){
			// $input['ins_prop_carrier'] = $input['ins_prop_carrier-other'];
			// $this->makelogInCarrierTable($input['ins_prop_carrier-other'],"Property");

			$input['ins_prop_carrier'] = $this->makelogInCarrierTable($input['ins_prop_carrier-other'],"Property");
		}
		if(!empty($input['general_liability']) && $input['general_liability'] == 'other'){
			// $input['general_liability'] = $input['general_liability-other'];
			// $this->makelogInCarrierTable($input['general_liability-other'],"General Liability");

			$input['general_liability'] = $this->makelogInCarrierTable($input['general_liability-other'],"General Liability");
		}
		$input['gl_exclusions'] = $request->input('gl_exclusions') ? implode(',', $request->input('gl_exclusions')) : '';
		if(!empty($input['crime_insurance']) && $input['crime_insurance'] == 'other'){
			// $input['crime_insurance'] = $input['crime_insurance-other'];
			// $this->makelogInCarrierTable($input['crime_insurance-other'],"Crime Insurance");

			$input['crime_insurance'] = $this->makelogInCarrierTable($input['crime_insurance-other'],"Crime Insurance");
		}
		if(!empty($input['directors_officers']) && $input['directors_officers'] == 'other'){
			// $input['directors_officers'] = $input['directors_officers-other'];
			// $this->makelogInCarrierTable($input['directors_officers-other'],"Directors & Officers");

			$input['directors_officers'] = $this->makelogInCarrierTable($input['directors_officers-other'],"Directors & Officers");
		}
		if(!empty($input['umbrella']) && $input['umbrella'] == 'other'){
			// $input['umbrella'] = $input['umbrella-other'];
			// $this->makelogInCarrierTable($input['umbrella-other'],"Umbrella");

			$input['umbrella'] = $this->makelogInCarrierTable($input['umbrella-other'],"Umbrella");
		}
		if(!empty($input['workers_compensation']) && $input['workers_compensation'] == 'other'){
			// $input['workers_compensation'] = $input['workers_compensation-other'];
			// $this->makelogInCarrierTable($input['workers_compensation-other'],"Workers Compensation");

			$input['workers_compensation'] = $this->makelogInCarrierTable($input['workers_compensation-other'],"Workers Compensation");
		}
		if(!empty($input['flood']) && $input['flood'] == 'other'){
			// $input['flood'] = $input['flood-other'];
			// $this->makelogInCarrierTable($input['flood-other'],"Flood");

			$input['flood'] = $this->makelogInCarrierTable($input['flood-other'],"Flood");
		}
		if(!empty($input['difference_in_condition']) && $input['difference_in_condition'] == 'other'){
			// $input['difference_in_condition'] = $input['difference_in_condition-other'];
			// $this->makelogInCarrierTable($input['difference_in_condition-other'],"Difference In Conditions");

			$input['difference_in_condition'] = $this->makelogInCarrierTable($input['difference_in_condition-other'],"Difference In Conditions");
		}
		if(!empty($input['x_wind']) && $input['x_wind'] == 'other'){
			// $input['x_wind'] = $input['x_wind-other'];
			// $this->makelogInCarrierTable($input['x_wind-other'],"X-Wind");

			$input['x_wind'] = $this->makelogInCarrierTable($input['x_wind-other'],"X-Wind");
		}
		if(!empty($input['equipment_breakdown']) && $input['equipment_breakdown'] == 'other'){
			// $input['equipment_breakdown'] = $input['equipment_breakdown-other'];
			// $this->makelogInCarrierTable($input['equipment_breakdown-other'],"Equipment Breakdown");

			$input['equipment_breakdown'] = $this->makelogInCarrierTable($input['equipment_breakdown-other'],"Equipment Breakdown");
		}
		if(!empty($input['commercial_automobiles']) && $input['commercial_automobiles'] == 'other'){
			// $input['commercial_automobiles'] = $input['commercial_automobiles-other'];
			// $this->makelogInCarrierTable($input['commercial_automobiles-other'],"Commercial AutoMobiles");

			$input['commercial_automobiles'] = $this->makelogInCarrierTable($input['commercial_automobiles-other'],"Commercial AutoMobile");
		}
		if(!empty($input['marina']) && $input['marina'] == 'other'){
			// $input['marina'] = $input['marina-other'];
			// $this->makelogInCarrierTable($input['marina-other'],"Marina");

			$input['marina'] = $this->makelogInCarrierTable($input['marina-other'],"Marina");
		}

		// ratings
		if(!empty($input['rating']) && $input['rating'] == 'other'){
			// $input['rating'] = $input['rating-other'];
			// $this->makelogInRatingTable($input['rating-other'],"Property");

			$input['rating'] = $this->makelogInRatingTable($input['rating-other'],"Property");
		}
		if(!empty($input['gl_rating']) && $input['gl_rating'] == 'other'){
			// $input['gl_rating'] = $input['gl_rating-other'];
			// $this->makelogInRatingTable($input['gl_rating-other'],"General Liability");

			$input['gl_rating'] = $this->makelogInRatingTable($input['gl_rating-other'],"General Liability");
		}
		if(!empty($input['ci_rating']) && $input['ci_rating'] == 'other'){
			// $input['ci_rating'] = $input['ci_rating-other'];
			// $this->makelogInRatingTable($input['ci_rating-other'],"Crime Insurance");

			$input['ci_rating'] = $this->makelogInRatingTable($input['ci_rating-other'],"Crime Insurance");
		}
		if(!empty($input['do_rating']) && $input['do_rating'] == 'other'){
			// $input['do_rating'] = $input['do_rating-other'];
			// $this->makelogInRatingTable($input['do_rating-other'],"Directors & Officers");

			$input['do_rating'] = $this->makelogInRatingTable($input['do_rating-other'],"Directors & Officers");
		}
		if(!empty($input['umbrella_rating']) && $input['umbrella_rating'] == 'other'){
			// $input['umbrella_rating'] = $input['umbrella_rating-other'];
			// $this->makelogInRatingTable($input['umbrella_rating-other'],"Umbrella");

			$input['umbrella_rating'] = $this->makelogInRatingTable($input['umbrella_rating-other'],"Umbrella");
		}
		if(!empty($input['wc_rating']) && $input['wc_rating'] == 'other'){
			// $input['wc_rating'] = $input['wc_rating-other'];
			// $this->makelogInRatingTable($input['wc_rating-other'],"Workers Compensation");

			$input['wc_rating'] = $this->makelogInRatingTable($input['wc_rating-other'],"Workers Compensation");
		}
		if(!empty($input['flood_rating']) && $input['flood_rating'] == 'other'){
			// $input['flood_rating'] = $input['flood_rating-other'];
			// $this->makelogInRatingTable($input['flood_rating-other'],"Flood");

			$input['flood_rating'] = $this->makelogInRatingTable($input['flood_rating-other'],"Flood");
		}
		//ordinance_of_law
		if(!empty($input['ordinance_of_law']) && $input['ordinance_of_law'] == 'other'){
			$input['ordinance_of_law'] = $input['ordinance_of_law-other'];
		}
		// echo "<pre>"; print_r($input); exit;
		$lead->update($input);
		$lead->contacts()->update(['c_is_client' => $request->is_client ? true : false]);


		LeadAdditionalPolicy::where('lead_id', $id)->delete();
	    foreach ($policyIds as $index => $policyId) {
	        // Check if carrier is "Other", then use "carrier{index}-other" value
	        $carrierOtherKey = "carrier{$index}-other";
	        $carrier = ($carriers[$index] === "other" && $request->has($carrierOtherKey) && !empty($request->$carrierOtherKey))
	                    ? $request->$carrierOtherKey
	                    : ($carriers[$index] ?? null);

	        if($carriers[$index] === "other"){
	        	$carrier_id = $this->makelogInCarrierTable($carrier,$policyTypes[$index]);
	        }
	        else{
	        	$carrier_id = $carrier;
	        }

	        if (!empty($carrier_id) && !empty($policyTypes[$index]) ) {
	            if (!empty($policyId)) {
	                // Update existing record
	                LeadAdditionalPolicy::withTrashed()->where('id', $policyId)->update([
	                    // 'lead_id' => $id,
	                    'carrier' => $carrier_id,
	                    'policy_type' => $policyTypes[$index],
	                    'expiry_premium' => $expiryPremiums[$index] ?? null,
	                    'policy_renewal_date' => $policyRenewalDates[$index] ?? null,
	                    'hurricane_deductible' => $hurricaneDeductibles[$index] ?? null,
	                    'all_other_perils' => $allOtherPerils[$index] ?? null,
	                    'insurance_coverage' => $insuranceCoverage[$index] ?? null,
	                    'deleted_at' => null,
	                ]);
	            } else {
	                LeadAdditionalPolicy::create([
	                    'lead_id' => $id,
	                    'carrier' => $carrier_id,
	                    'policy_type' => $policyTypes[$index],
	                    'expiry_premium' => $expiryPremiums[$index] ?? null,
	                    'policy_renewal_date' => $policyRenewalDates[$index] ?? null,
	                    'hurricane_deductible' => $hurricaneDeductibles[$index] ?? null,
	                    'all_other_perils' => $allOtherPerils[$index] ?? null,
	                    'insurance_coverage' => $insuranceCoverage[$index] ?? null,
	                ]);
	            }
	        }
	    }

	    $this->leadTotalPremiumUpdate($id);

		//create log
		create_log($lead, 'Edit Lead', '');
		flash()->success('Business ' . $lead->name . ' updated successfully');
		return redirect()->back();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//find the lead to delete
		$lead = Lead::find($id);
		if (!$lead) {

			toastr()->error('The Lead was removed previously');
			return back();
		}
		// rename lead if deleted - to fix the Unique issue
		// $lead->update([
		// 	'name' => time() . '::' . $lead->name
		// ]);
		//remove everything related to lead
		$lead->contacts()->delete();
		$lead->logs()->delete();
		$lead->files()->delete();
		$lead->actions()->delete();
		$lead->notes()->delete();
		$lead->delete();
		//if restore , here is the code needed:
		// Lead::withTrashed()->find($id)->restore();
		// Lead::withTrashed()->find($id)->contacts()->restore();
		// Lead::withTrashed()->find($id)->logs()->restore();
		// Lead::withTrashed()->find($id)->files()->restore();
		// Lead::withTrashed()->find($id)->actions()->restore();
		// Lead::withTrashed()->find($id)->notes()->restore();

		toastr()->success('Lead <b>' . $lead->name . '</b> Deleted!');
		return  redirect()->route('leads.index');
	}

	/**------------------------------------------------------------*
	 *                  Current Insurances on Lead
	 * **------------------------------------------------------------*
	 *
	 * Update the specified resource in storage.
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update_current_insurance(Request $request, $id)
	{
		$rules = [
			'general_liability' => 'nullable|string|max:191',
			'GL_ren_month' => 'nullable|string|max:191',
			'crime_insurance' => 'nullable|string|max:191',
			'CI_ren_month' => 'nullable|string|max:191',
			'directors_officers' => 'nullable|string|max:191',
			'DO_ren_month' => 'nullable|string|max:191',
			'workers_compensation' => 'nullable|string|max:191',
			'WC_ren_month' => 'nullable|string|max:191',
			'umbrella' => 'nullable|string|max:191',
			'U_ren_month' => 'nullable|string|max:191',
			'flood' => 'nullable|string|max:191',
			'F_ren_month' => 'nullable|string|max:191'
		];
		$niceNames = [
			'general_liability' => 'General Liability',
			'GL_ren_month' => 'General Liability Renewal Month',
			'crime_insurance' => 'Crime Insurance',
			'CI_ren_month' => 'Crime Insurance Renewal Month',
			'directors_officers' => 'Directors & Officers',
			'DO_ren_month' => 'Directors & Officers Renewal Month',
			'workers_compensation' => 'Workers Compensation',
			'WC_ren_month' => 'Workers Compensation Renewal Month',
			'umbrella' => 'Umbrella',
			'U_ren_month' => 'Umbrella Renewal Month',
			'flood' => 'Flood',
			'F_ren_month' => 'Flood General Liability Renewal Month'
		];
		//validate fields using nice name in error messages
		$this->validate($request, $rules, [], $niceNames);
		$input = $request->all();
		//create the new lead
		$lead = Lead::find($id);
		$lead->update($input);
		$changes = $lead->getChanges();

		if (count($changes) > 0) {
			foreach ($changes as $key => $change) {
				if ($key != "updated_at") {
					//create log
					create_log($lead, 'Update Current Insurance - ' . $niceNames[$key], '');
				}
			}
			toastr()->success('Current Insurance for <b>' . $lead->name . '</b> edited successfully');
		} else {
			toastr()->error('No changes to Current Insurance');
		}
		return redirect()->back();
	}
	/**------------------------------------------------------------*
	 *                  Campaign - Create & Export CSV
	 *------------------------------------------------------------*
	 *
	 * Create campaign
	 * $filters - selected options
	 * $campaignName - the campaign name
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function save_campaign(Request $request)
	{

		$filters = (!empty($request->searchFields1)) ? ($request->searchFields1) : (''); //get search filters
		$campaignName =  (!empty($request->name)) ? ($request->name) : (''); //get campaign name
		$campaignId =  (!empty($request->campaign)) ? ($request->campaign) : (''); //get campaign name
		$location_leads_id = (!empty($request->location_leads_id)) ? json_decode($request->location_leads_id) : ('');
		$location_leads_id_search = (!empty($request->location_leads_id_search)) ? $request->location_leads_id_search : false;

		$mail_agent_id = auth()->user()->id;
		// echo $mail_agent_id;exit;

		$mail_smtp_check = $this->checkMailConfigurationUserWise($mail_agent_id);
		
		if($mail_smtp_check == 0){
			return response()->json(array('status' => false, 'message' => 'You do not have SMTP configuration. Please set it up before attempting to create Mailing List.'));
		}
		CreateCampaignJob::dispatch($filters,$campaignName,$campaignId,$location_leads_id,$location_leads_id_search,$mail_agent_id);

		return response()->json(array('status' => true, 'message' => 'Once the process to create the campaign is initialized, we will notify you via email.'));
	}


	/**------------------------------------------------------------*
	 *                  WebHook
	 *------------------------------------------------------------*
	 *
	 * Webhook for Ricochet
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function getLead(Request $request)
	{
		$this->validate($request, [

			'token' => 'required',
			'phone' => 'required',
		]);

		if ($request->token == 'GrNB7jTaUIXC9o0EBGOTqB3ME6tQDVLp') {

			$phone = $request->phone;
			if (substr($phone, 0, 1) == 1) {
				$phone = substr($phone, 1); //remove the 1 received from ricochet
			}
			//format phone - remove any chars that are not numbers
			if (preg_match('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', $phone, $matches)) {
				$phone =  preg_replace('/[-!$%^&*()_+|~=`{}\[\]:";<>?,. \/]/', '', $phone);
			}
			$contact = Contact::with('leads')->where('c_phone', 'like', $phone)->first(); //get contact

			if ($contact) {
				//redirect to lead page, showing the contact
				return redirect()->route('leads.edit', ['id' => $contact->leads->id, 'contact_id' => $contact->id, 'contact_phone' => $contact->c_phone]);
				//return response()->json(['message'=>'Success','data'=>$contact]); // returns the contact&lead data

			} else {
				return response()->json(['error' => 'Contact not found ']);
			}
		}
		abort(404);
	}

	/**
	 * Display remove bulk Leads page
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function remove_leads()
	{

		return view('leads.remove_leads');
	}

	public function delete_leads(Request $request)
	{


		$leadIds = $request->selectedValues;
		if (count($leadIds) <= 0) :
			return response()->json(['leadsCount' => 0, 'message' => 'Please check at least one checkbox to continue.']);
		endif;

		// Retrieve leads based on the array of IDs
		$leads = Lead::whereIn('id', $leadIds)->get();


		// Loop through the leads
		foreach ($leads as $lead) {
			// Delete all contacts related to the lead
			$lead->contacts()->delete();

			// Detach all contacts related to the lead
			// $lead->contacts()->detach();

			// Delete all logs related to the lead
			$lead->logs()->delete();

			// Delete all files related to the lead
			$lead->files()->delete();

			// Delete all actions related to the lead
			$lead->actions()->delete();

			// Delete all notes related to the lead
			$lead->notes()->delete();

			// Soft delete the lead
			$lead->delete();

			// Create a log entry for soft deleting the lead and related data
			create_log($lead, 'Soft Deleted all related datas for ' . $lead->id, '');
		}

		return response()->json(['leadsCount' => 1, 'message' => 'Records deleted successfully']);
	}


	/**
	 * Read data from csv file
	 * @param object $csvFile
	 * @return array $csvData
	 */
	private static function readDataFromCsv($csvFile, $extension)
	{

		//store file
		$fileName = Carbon::now()->format('mdYHisu');
		//if the file is xlsx or xls , convert it to csv
		if ($extension == "xlsx" || $extension == "xls") {
			if ($extension == "xlsx") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			} else if ($extension == "xls") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
			}

			$reader->setReadDataOnly(true);


			$path = '../storage/app/public/uploads/' . $fileName . '.csv';
			$excel = $reader->load($csvFile);
			// dd($excel);
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
			// $writer->setUseBOM(true);
			// $writer->setOutputEncoding('UTF-8');
			$writer->setUseBOM(false);
			$writer->setOutputEncoding('UTF-8');
			$writer->setEnclosureRequired(false);
			$writer->save($path);

			$csvFile =  $path;
		} else {

			$file = Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
		}

		$delimiter = ',';
		$header = null;
		$csvData = array();
		//the required columns
		$requiredColumns = array(
			1 => "Business_Name",
		);
		//read data and add it to array
		if (($handle = fopen($csvFile, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

				if (!$header) {
					$header = $row;
					//loop trough required columns and if one of them is missing in csv, send error
					foreach ($requiredColumns as $req) {

						if (!in_array($req, $header)) {

							$ColumnError =  'Column ' . $req . ' is missing. File was not parsed.';
							return array('errors' => $ColumnError);
						}
					}
				} else {

					if (count($header) > count($row)) {

						$csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), "")), 'UTF-8', 'UTF-8');
					} else if (count($header) < count($row)) {
						$csvData[] =  mb_convert_encoding(array_combine($header, array_slice($rows, 0, count($header))), 'UTF-8', 'UTF-8');
					} else {
						$csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
					}
				}
			}
			fclose($handle);
		}
		return $csvData;
	}

	/*** Add or update filter ***/
	public function filterStore(Request $request)
	{
		$filters = $request->filters;
		if ($filters && $request->has('save_filter_name') && $request->save_filter_name) {
			$filter_id = $request->has('save_filter_id') ? $request->save_filter_id : 0;
			$save_filter_name = $request->has('save_filter_name') ? $request->save_filter_name : '';
			if ($save_filter_name) {
				$filter = $filter_id ? Filter::where([['id', '!=', $filter_id], ['name', '=', $save_filter_name]])->first() : Filter::where('name', '=', $save_filter_name)->first();
				if ($filter) {
					return array('status' => false, 'message' => 'Name for the filter should unique');
				}
			}
			$filter_add_update = [];
			$filter_add_update['conditions'] = $filters;
			$filter_add_update['name'] = $save_filter_name;
			$filters = json_decode($filters, true);
			if (array_key_exists(0, $filters)) {
				$address_text = $filters[0][0]['address_text'];
				$distance_op = $filters[0][0]['distance_op'];
				$distance_query_selection_checkbox = $filters[0][0]['distance_query_selection_checkbox'];
				$lead_business_names_search = $filters[0][0]['lead_business_names_search'];
				$lead_business_name_search_id = $filters[0][0]['lead_business_name_search_id'];
				if ($distance_query_selection_checkbox == 'true' && $lead_business_names_search && $lead_business_name_search_id > 0) {
					$lead = Lead::find($lead_business_name_search_id);
					if ($lead) {
						$address_text = ($lead->address1 && $lead->address2) ? $lead->address1 . ' ' . $lead->address2 : ($lead->address1 ? $lead->address1 : $lead->address2);
					}
				}
				$lat_long = parent::getLatLngFromGoogle($address_text);
				if (is_null($lat_long['lat']) && is_null($lat_long['long'])) {
					return array('status' => false, 'message' => 'Please add valid address');
				}
				$filter_add_update['latitude'] = $lat_long['lat'];
				$filter_add_update['longitude'] = $lat_long['long'];
				$filter_add_update['operator'] = $distance_op;
				$filter_add_update['address'] = $address_text;
				$filter_add_update['distance'] = $filters[0][0]['distance'];
				$filter_add_update['is_business_name'] = $filters[0][0]['distance_query_selection_checkbox'];
				$filter_add_update['business_name'] = $filters[0][0]['lead_business_names_search'];
				$filter_add_update['business_id'] = $filters[0][0]['lead_business_name_search_id'];
			}
			$message = '';
			$id = 0;
			if ($filter_id) {
				$filter = Filter::find($filter_id);
				$filter->update($filter_add_update);
				$id = $filter_id;
				$message = 'Filter updated successfully';
			} else {
				$filter = Filter::create($filter_add_update);
				$id = $filter->id;
				$message = 'Filter created successfully';
			}
			return array('status' => true, 'message' => $message, 'id' => $id);
		}
		return array('status' => false, 'message' => 'Something went wrong');
	}

	/*** Delete filter ***/
	public function filterDelete(Request $request)
	{
		$filter = Filter::find($request->id);
		if ($filter) {
			$filter->delete();
			return array('status' => true, 'message' => 'Filter deleted successfully');
		}
		return array('status' => false, 'message' => 'No filter found');
	}

	/*** Custom search leads ***/
	public function customSearch(Request $request)
	{
		$names = Lead::select('id', 'name')->where('type', $request->type)->where('name', 'like', '%' . $request->term . '%')->whereNotNull('latitude')->get();
		return array('result' => $names);
	}

	/*** Get leads from latitude and longitude ***/
	public function getLeadsIdFromLocation(Request $request)
	{
		$distance_op = $request->map_marker_distance_op;
		$distance = $request->map_marker_distance;
		$latitude = $request->latitude;
		$longitude = $request->longitude;
		$leads = Lead::whereIn('id', function ($query) use ($latitude, $longitude, $distance_op, $distance) {
			$query->from('leads')->selectRaw("id")->whereRaw("SQRT(POW(69.1 * (latitude - $latitude), 2) + POW(69.1 * ($longitude - longitude) * COS(latitude / 57.3), 2)) $distance_op $distance");
		})->pluck('id')->toArray();
		return array('leads_id' => $leads);
	}

	public function getAllLeadsLocation(Request $request)
	{
		$is_clientData = $request->is_client;
		$leadQuery = Lead::select('id', 'latitude', 'longitude', 'name', 'is_client')->whereNull('deleted_at');

		if ($is_clientData == '1') {
			$leadQuery->where('is_client', 1);
		}

		$leads = $leadQuery->get();
		$locations = [];
		// $leads = $leadQuery;
		if ($leads) {
			foreach ($leads as $lead) {
				if (is_numeric($lead->latitude) && is_numeric($lead->longitude)) {
					array_push($locations, array('id' => $lead->id, 'name' => $lead->name, 'latitude' => $lead->latitude, 'longitude' => $lead->longitude));
				}
			}
		}
		return ['data' => $locations];
	}

	public function updateHistoricalLeadTotalPremium()
	{
		Lead::select("id")
        ->chunk(100, function ($leads) {
            foreach ($leads as $lead) {
                $this->leadTotalPremiumUpdate($lead->id);
            }
        });
	}
}
