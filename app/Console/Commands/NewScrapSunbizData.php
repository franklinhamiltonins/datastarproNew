<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use Illuminate\Support\Facades\DB;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

use App\Traits\SunbizDataTrait;

class NewScrapSunbizData extends Command
{
    use SunbizDataTrait;

    protected $signature = 'newLogic:SunBizScrap {looplimit?}';
    protected $description = 'new logic for scrap sunbiz Api';

    private $responseArr = [
        'list_url' => null,
        'details_url' => null,
        'principal_address' => null,
        'mailing_address' => null,
        'registered_name' => null,
        'registered_address' => null,
        'members' => []
    ];

    public function handle()
    {
        $max_limit = $this->argument('looplimit'); 
        $entry_made = 0;
        $chunkSize = 50;
        $stopLoop = false;

        while (Lead::where('is_added_by_bot', 2)->first()) {
            Lead::where('is_added_by_bot', 2)
            ->orderBy('id', 'asc')
            ->select('id', 'name')
            ->chunk($chunkSize, function ($leads) use (&$entry_made, $max_limit,&$stopLoop) {
                foreach ($leads as $lead) {
                    try{
                        DB::beginTransaction();

                        $this->getContactDetails($lead->id);
                        $entry_made++;

                        Lead::where('id', $lead->id)->update(["is_added_by_bot"=> 3]);

                        DB::commit();
                    }
                    catch(\Exception $e){
                        DB::rollBack();
                        Lead::where('id', $lead->id)->update(["is_added_by_bot"=> 5]);
                        \Log::error("SunBiz Scraper Error for lead ID {$lead->id}: " . $e->getMessage());
                        // echo $lead->id. "  ".$e->getMessage(); exit;
                        $stopLoop = true;
                        return false;
                    }

                    // echo "<pre>";print_r($lead);exit;

                    if (!empty($max_limit) && $entry_made >= $max_limit) {
                        $stopLoop = true;
                        return false;
                    }
                }
            });

            if ($stopLoop) {
                break; // manually break while loop
            }
        }

        $this->info("{$entry_made} Leads operation has been done");
    }

    private function getContactDetails($lead_id)
    {
        $lead = Lead::find($lead_id);
        if (!$lead) return;

        $url = $lead->sunbiz_details_url;
        if(empty($url)){
            Lead::where('id', $lead->id)->update(["is_added_by_bot"=> 4]);
            return [];
        }
        $list_url = $lead->sunbiz_list_url;

        $client = new Client();
        $crawler = $client->request('GET', $url);
        if (!$crawler) return [];

        $this->responseArr['list_url'] = $list_url;
        $this->responseArr['details_url'] = $url;

        $finalArrres = $this->crawlResponseAddress($crawler);
        $finalArr = $finalArrres['responsearr'];
        $data = $finalArrres['responsedata'];

        $members = $this->extractMemberDetails($crawler, $data);
        $this->saveMembers($members, $lead_id);

        if (!empty($finalArr['registered_name']) || !empty($finalArr['registered_address'])) {
            Lead::where('id', $lead_id)->update([
                'sunbiz_registered_name' => $finalArr['registered_name'],
                'sunbiz_registered_address' => $finalArr['registered_address']
            ]);
        }

        return $finalArr;
    }

    private function crawlResponseAddress($crawler)
    {
        $finalArr = $this->responseArr;
        $data = [];

        $spans = $crawler->filter('div.detailSection > span');
        for ($i = 0; $i < $spans->count(); $i++) {
            $text = trim($spans->eq($i)->text());
            $data[] = $text;

            if ($text === "Principal Address") {
                $finalArr['principal_address'] = trim($spans->eq($i + 1)->text());
            } elseif ($text === "Mailing Address") {
                $finalArr['mailing_address'] = trim($spans->eq($i + 1)->text());
            } elseif ($text === "Registered Agent Name & Address") {
                $finalArr['registered_name'] = trim($spans->eq($i + 1)->text());

                $addressDiv = $spans->eq($i + 2)->filter('div');
                if ($addressDiv->count() > 0) {
                    $rawHtml = $addressDiv->html();
                    $addressLines = array_filter(array_map(function ($line) {
                        return trim(strip_tags($line));
                    }, preg_split('/<br[^>]*>/i', $rawHtml)));

                    $finalArr['registered_address'] = implode(' ', $addressLines);
                }
            }
        }

        return [
            'responsearr' => $finalArr,
            'responsedata' => $data
        ];
    }

    private function extractMemberDetails($crawler, $data)
    {
        $sections = $crawler->filter('.detailSection');
        $membersNames = [];
        $members = [];

        foreach ($sections as $section) {
            $crawler = new Crawler($section->ownerDocument->saveHTML($section));
            $crawler->filterXPath('//div[@class="detailSection"]/text()')->each(function ($node) use (&$membersNames) {
                $val = trim($node->text());
                if (!empty($val)) $membersNames[] = $val;
            });
        }

        $selected_index = $this->findStartIndex($data);

        if ($selected_index) {
            for ($j = $selected_index; $j <= count($data); $j += 2) {
                if (!isset($data[$j]) || $data[$j] === "Annual Reports") break;

                $members[] = [
                    'member_title' => isset($data[$j]) ? trim(preg_replace('/^Title\s*/', '', $data[$j])) : '',
                    'member_address' => $data[$j + 1] ?? ''
                ];
            }
        }

        return $this->combineNamesWithMembers($membersNames, $members);
    }

    private function findStartIndex($data)
    {
        $officerIndex = array_search("Officer/Director Detail", $data);
        if ($officerIndex === false) {
            $officerIndex = array_search("Authorized Person(s) Detail", $data);
        }
        $nameAddrIndex = array_search("Name & Address", $data);

        return ($officerIndex !== false && $nameAddrIndex !== false) ? $officerIndex + 2 : 0;
    }

    private function combineNamesWithMembers($names, $members)
    {
        if (count($members) === 0 || count($names) !== count($members)) return [];

        foreach ($members as $i => &$member) {
            $full = $names[$i];
            $first = $full;
            $last = '';

            if (strpos($full, ',') !== false) {
                $parts = preg_split('/,\s*/', $full);
                $first = end($parts);
                $last = implode(' ', array_slice($parts, 0, -1));
            }

            $member['member_name'] = trim("$first $last");
            $member['first_name'] = $first;
            $member['last_name'] = $last;
        }

        return $members;
    }

    
}