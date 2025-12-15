<?php

namespace App\Services;


use Illuminate\Support\Facades\Http;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use App\Traits\CommonFunctionsTrait;
use App\Model\Setting;
use DB;
use App\Model\LeadsModel\Lead;

use Spatie\Browsershot\Browsershot;


class GetSunBizDetailsBasic
{
    use CommonFunctionsTrait;

    public function replaceSubstrings($string) {

        $replacements = [
            " ASSOC "   => " ASSOCIATION ",
            " ASSC "    => " ASSOCIATION ",
            " ASSN "    => " ASSOCIATION ",
            " APRTMNTS " => " APPARTMENTS ",
            " AVE "     => " AVENUE ",
            " BCH "     => " BEACH ",
            " BLDG "    => " BUILDING ",
            " CLB "     => " CLUB ",
            " COMM "    => " COMMERCIAL ",
            " CMNTY "   => " COMMUNITY ",
            " CONDO "   => " CONDOMINIUM ",
            " CNDO "    => " CONDOMINIUM ",
            " CONDOS "  => " CONDOMINIUM ",
            " CNDMMS "  => " CONDOMINIUM ",
            " CLRWTER " => " CLEARWATER ",
            " CTR "     => " CENTER ",
            " DSTIN "   => " DISTINCT ",
            " POA "     => " PROPERTY OWNERS' ASSOCIATION ",
            " PROF "    => " PROFESSIONAL ",
            " PRFSSNL " => " PROFESSIONAL ",
            " PRTNERS " => " PARTNERS ",
            " STN "     => " STATION ",
            " ST "      => " STREET ",
            " SNSHINE " => " SUNSHINE ",
            " TWERS "   => " TOWERS ",
            " HLMES "   => " HOLMES ",
            " MED "     => " MEDICAL ",
            " CLNY "    => " COLONY ",
            " MASTER "  => " MASTER ",
            " HSE "     => " HOUSE ",
            " HSES "    => " HOUSES ",
            " HMOWNERS " => " HOMEOWNERS ",
            " BRCKELL " => " BRICKELL ",
            " BLVD "    => " Boulevard ",
            " DRV "     => " DRIVE ",
            " VLG "     => " VILLAGE ",
            " LK "      => " LAKE ",
            " MNGROVE " => " MANGROVE ",
            " ASSOCIATES " => " ASSOCIATION ",
            " HBR "     => " HARBOR ",
            " EGLE "    => " EAGLE ",
            " PT "      => " POINT ",
            " PNTE "    => " POINTE ",
            " VDRA "    => " VEDRA ",
            " RSORT "   => " RESORT ",
            " CNTRY "   => " COUNTRY ",
            " CORP "    => " CORPORATION ",
            " ADM "     => " ADMINISTRATIVE ",
            " MGT "     => " MANAGEMENT ",
            " PK "      => " PARK ",
            " FREST "   => " FOREST ",
            " FLMING "  => " FLEMING ",
            " TWNHSES " => " TOWNHOUSES ",
            " CCO "     => " COCOA ",
            " GRDNS "   => " GARDENS ",
            " SCTION "  => " SECTION ",
            " RSDNCE "  => " RESIDENCE ",
            " PL "      => " PLACE ",
            " TNEY "    => " TONEY ",
            " PNNA "    => " PENNA ",
            " HTS "     => " HEIGHTS ",
            " VNDRBILT " => " VANDERBILT ",
            " SMNOLE "  => " SEMINOLE ",
            " TSCANY "  => " TUSCANY ",
            " COML "    => " COMMERCIAL ",
            " S "       => " SOUTH ",
            " ORNGE "   => " ORANGE ",
            " SNST "    => " SUNSET ",
            " NE "      => " NEIGHBORHOOD ",
            " CNWAY "   => " CONWAY ",
            " WODS "    => " WOODS ",
            " LNDS "    => " LANDS ",
            " PR "      => " PRESIDENT ",
            " TR "      => " TERRACE ",
            " FRTY "    => " FORTY ",
            " PRPRTY "  => " PROPERTIES ",
            " BOCA W "  => " BOCA WEST ",
            " RE "      => " REAL ESTATE ",
            " HNTERS "  => " HUNTERS ",
            " JPITER "  => " JUPITER ",
            " MGNLIA "  => " MAGNOLIA ",
            " SQ "      => " SQUARE ",
            " MAMI "    => " MIAMI ",
            " HRITG "   => " HERITAGE ",
            " DGLAS "   => " DOUGLAS ",
            " RDGE "    => " RIDGE ",
            " SRSOTA "  => " SARASOTA ",
            " TURNBRRY " => " TURNBERRY ",
            " DNES "    => " DUNES ",
            " RVGOLF "  => " R.V./GOLF ",
            " E "       => " EAST ",
            " W "       => " WEST ",
            " RCRTL "   => " RECREATIONAL ",
            " VHCL "    => " VEHICLE ",
            " PRKG "    => " PARKING ",
            " CMMRCE "  => " COMMERCE ",
            " BUS "     => " BUSINESS ",
        ];

        $string = $string." ";
        foreach ($replacements as $search => $replace) {
            $string = str_replace($search, $replace, $string);
        }
        
        return trim($string);
    }

    public function scrap_sunbiz($lead_name = 'Ocean 14')
    {

        $lead_name = strtoupper($lead_name);
        $entity_name = str_replace('', '%20', $lead_name);
        $searchNameOrder = strtoupper(str_replace(' ', '', $lead_name));

        $list_url = 'https://search.sunbiz.org/Inquiry/CorporationSearch/SearchResults/EntityName/' . $entity_name . '/Page1?searchNameOrder=' . $searchNameOrder;
        // $response = Http::get($list_url);

        try {

            $html = Browsershot::url($list_url)
                ->timeout(60)
                ->waitUntilNetworkIdle()
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
                ->bodyHtml();

        } catch (\Exception $e) {

            Log::error("Browsershot failed for URL: " . $list_url . " | " . $e->getMessage());
            return [];
        }

        $crawler = new \Symfony\Component\DomCrawler\Crawler($html);

        // $crawler = new Crawler($response->body());
        $entity_name_probability_arr = ['CONDOMINIUM', 'ASSOCIATION', 'INC', 'LLC', 'LIMITED'];
        $scrap_response = [];
        $match_found = false;

        $crawler->filter('[id^="search-results"]>table>tbody>tr')->each(function ($node) use (&$scrap_response, &$match_found, $lead_name, $entity_name_probability_arr, $list_url) {
            $status = $node->filter('.small-width')->text();

            if ($status != 'Active') {
                return;
            }

            $business_name = $node->filter('.large-width')->text();
            $business_name_href = $node->filter('.large-width > a')->attr('href');
            $document_number = $node->filter('.medium-width')->text();

            // echo "<pre>";print_r($business_name);print_r($business_name_href);print_r($document_number);exit;

            $similarity = $this->calculateSimilarity($lead_name, $business_name, $entity_name_probability_arr);


            if ($similarity['similarity'] >= 0.5 && !$match_found) {
                $status = ($lead_name === $business_name) ? 'selected' : 'not_selected';
                $scrap_response['handle_data'] = $this->getcontactDetails($business_name_href, $list_url);

                // echo "<pre>";print_r($scrap_response);exit;
                $match_found = true;
            }
        });
        // dd($scrap_response);
        return $scrap_response;
    }

    public function calculateSimilarity($lead_name, $business_name, $entity_name_probability_arr)
    {
        $original_business_name = $business_name;
        
        // $business_name = $this->replaceSubstrings(strtoupper($business_name), $replacements);
        echo $business_name."====";
        echo $lead_name;
        echo "-------------------------------------------<br>------------------------------";
        
        $lead_name = strtolower(str_replace([' ', '(', ')', '.', ',', '\''], '', $lead_name));
        $business_name = strtolower(str_replace([' ', '(', ')', '.', ',', '\''], '', $business_name));

        $similarity = 0;
        if (strpos($business_name, $lead_name) !== false) {
            $similarity += 0.5;
        }
        foreach ($entity_name_probability_arr as $entity) {
            if (strpos($business_name, $entity) !== false) {
                $similarity += 0.1;
            }
        }
        echo 'similarity' . $similarity;

        return ['similarity' => $similarity, 'business_name' => $original_business_name];
    }

    public function getcontactDetails($url, $list_url)
    {
        $fullUrl = 'https://search.sunbiz.org' . $url;

        // echo $fullUrl;exit;

        $client = new Client();
        $crawler = $client->request('GET', $fullUrl);

        // echo "<pre>";print_r($crawler);exit;

        if (!$crawler) {
            return [];
        }

        $finalArr = [
            'list_url'          => $list_url,
            'details_url'       => $fullUrl,
            'principal_address' => null,
            'mailing_address'   => null,
            'registered_name'   => '',
            'registered_address'=> '',
            'members'           => []
        ];

        $spans = $crawler->filter('div.detailSection > span');
        $data  = [];

        $spanCount = $spans->count();
        for ($i = 0; $i < $spanCount; $i++) {
            $text = trim($spans->eq($i)->text());
            $data[] = $text;

            switch ($text) {
                case "Principal Address":
                    $finalArr['principal_address'] = trim($spans->eq($i + 1)->text());
                    break;

                case "Mailing Address":
                    $finalArr['mailing_address'] = trim($spans->eq($i + 1)->text());
                    break;

                case "Registered Agent Name & Address":
                    $finalArr['registered_name'] = trim($spans->eq($i + 1)->text());

                    $addressDiv = $spans->eq($i + 2)->filter('div');
                    if ($addressDiv->count()) {
                        $rawHtml = $addressDiv->html();
                        $addressLines = array_filter(array_map('trim',
                            preg_split('/<br[^>]*>/i', strip_tags($rawHtml, '<br>'))
                        ));
                        $finalArr['registered_address'] = implode(' ', $addressLines);
                    }
                    break;
            }
        }

        $membersNames = [];
        $sections = $crawler->filter('.detailSection');

        foreach ($sections as $section) {
            $crawler = new Crawler($section->ownerDocument->saveHTML($section));
            $crawler->filterXPath('//div[@class="detailSection"]/text()')->each(function ($node) use (&$membersNames) {
                $val = trim($node->text());
                if (!empty($val)) $membersNames[] = $val;
            });
        }

        $officerIndex = array_search("Officer/Director Detail", $data);
        if ($officerIndex === false) {
            $officerIndex = array_search("Authorized Person(s) Detail", $data);
        }

        $nameAddrIndex = array_search("Name & Address", $data);

        if ($officerIndex === false || $nameAddrIndex === false) {
            return $finalArr; // nothing to extract
        }

        $readIndex = $officerIndex + 2;
        $dataCount = count($data);

        $members = [];
        while ($readIndex < $dataCount) {

            if ($data[$readIndex] === "Annual Reports") {
                break;
            }

            $title = isset($data[$readIndex]) ? preg_replace('/^Title\s*/', '', $data[$readIndex]) : "";
            $address = $data[$readIndex + 1] ?? "";

            $members[] = [
                'member_title'   => trim($title),
                'member_address' => trim($address)
            ];

            $readIndex += 2;
        }
        // echo "<pre>";print_r($members);print_r($membersNames);exit;

        if (count($members) && count($membersNames) && count($members) === count($membersNames)) {

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
            }

            $finalArr['members'] = $members;
        }

        return $finalArr;
    }
    
}