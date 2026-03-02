<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class TestPurposeController extends Controller
{
    public function getcontactDetails(Request $r)
    {

    	echo '<form name="test" method="GET" action="">

    				<input type="text" name="lead_id" value="'.$r->lead_id.'"  placeholder="Enter Lead ID"/>
    				<input type="submit" value="Get Info" />
    			</form>
    		';

    	if($r->input('lead_id')){

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

    		$scrap_response = $getSunBiz->scrapSunbiz($leadName);

    		echo "<pre>";print_r($scrap_response);exit;
    	}

    }

}
