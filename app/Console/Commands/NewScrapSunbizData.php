<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use App\Traits\SunbizDataTrait;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Command to scrape Sunbiz data with new logic
 */
class NewScrapSunbizData extends Command
{
    use SunbizDataTrait;

    /** @var string Command signature */
    protected $signature = 'newLogic:SunBizScrap {looplimit?}';

    /** @var string Command description */
    protected $description = 'new logic for scrap sunbiz Api';

    // Default response array structure
    private const DEFAULT_RESPONSE = [
        'list_url' => null,
        'details_url' => null,
        'principal_address' => null,
        'mailing_address' => null,
        'registered_name' => null,
        'registered_address' => null,
        'members' => [],
    ];

    // Lead status constants
    private const LEAD_BOT_STATUS_CRAWLED = 2;
    private const LEAD_BOT_STATUS_DETAILS_FETCHED = 3;
    private const LEAD_BOT_STATUS_NO_URL = 4;
    private const LEAD_BOT_STATUS_ERROR = 5;

    // Instance response array
    private $responseArr = [];

    public function __construct()
    {
        parent::__construct();
        $this->responseArr = self::DEFAULT_RESPONSE;
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        $maxLimit = $this->argument('looplimit');
        $entryMade = 0;
        $chunkSize = 50;
        $stopLoop = false;

        while (Lead::where('is_added_by_bot', self::LEAD_BOT_STATUS_CRAWLED)->first()) {
            Lead::where('is_added_by_bot', self::LEAD_BOT_STATUS_CRAWLED)
                ->orderBy('id', 'asc')
                ->select('id', 'name')
                ->chunk($chunkSize, function ($leads) use (&$entryMade, $maxLimit, &$stopLoop) {
                    foreach ($leads as $lead) {
                        try {
                            DB::beginTransaction();

                            $this->getContactDetails($lead->id);
                            $entryMade++;

                            Lead::where('id', $lead->id)->update(['is_added_by_bot' => self::LEAD_BOT_STATUS_DETAILS_FETCHED]);

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Lead::where('id', $lead->id)->update(['is_added_by_bot' => self::LEAD_BOT_STATUS_ERROR]);
                            \Log::error("SunBiz Scraper Error for lead ID {$lead->id}: " . $e->getMessage());
                            $stopLoop = true;

                            return false;
                        }

                        if (!empty($maxLimit) && $entryMade >= $maxLimit) {
                            $stopLoop = true;

                            return false;
                        }
                    }
                });

            if ($stopLoop) {
                break;
            }
        }

        $this->info("{$entryMade} Leads operation has been done");
    }

    /**
     * Get contact details from Sunbiz for a lead
     */
    private function getContactDetails($leadId)
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            return;
        }

        $url = $lead->sunbiz_details_url;
        if (empty($url)) {
            Lead::where('id', $lead->id)->update(['is_added_by_bot' => self::LEAD_BOT_STATUS_NO_URL]);

            return [];
        }
        $listUrl = $lead->sunbiz_list_url;

        $client = new Client;
        $crawler = $client->request('GET', $url);
        if (!$crawler) {
            return [];
        }

        $this->responseArr['list_url'] = $listUrl;
        $this->responseArr['details_url'] = $url;

        $finalArrRes = $this->crawlResponseAddress($crawler);
        $finalArr = $finalArrRes['responsearr'];
        $data = $finalArrRes['responsedata'];

        $members = $this->extractMemberDetails($crawler, $data);
        $this->saveMembers($members, $leadId);

        if (!empty($finalArr['registered_name']) || !empty($finalArr['registered_address'])) {
            Lead::where('id', $leadId)->update([
                'sunbiz_registered_name' => $finalArr['registered_name'],
                'sunbiz_registered_address' => $finalArr['registered_address'],
            ]);
        }

        return $finalArr;
    }

    /**
     * Crawl response address from the page
     */
    private function crawlResponseAddress($crawler)
    {
        $finalArr = $this->responseArr;
        $data = [];

        $spans = $crawler->filter('div.detailSection > span');
        for ($i = 0; $i < $spans->count(); $i++) {
            $text = trim($spans->eq($i)->text());
            $data[] = $text;

            if ($text === 'Principal Address') {
                $finalArr['principal_address'] = trim($spans->eq($i + 1)->text());
            } elseif ($text === 'Mailing Address') {
                $finalArr['mailing_address'] = trim($spans->eq($i + 1)->text());
            } elseif ($text === 'Registered Agent Name & Address') {
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
            'responsedata' => $data,
        ];
    }

    /**
     * Extract member details from the page
     */
    private function extractMemberDetails($crawler, $data)
    {
        $sections = $crawler->filter('.detailSection');
        $membersNames = [];
        $members = [];

        foreach ($sections as $section) {
            $crawler = new Crawler($section->ownerDocument->saveHTML($section));
            $crawler->filterXPath('//div[@class="detailSection"]/text()')->each(function ($node) use (&$membersNames) {
                $val = trim($node->text());
                if (!empty($val)) {
                    $membersNames[] = $val;
                }
            });
        }

        $selectedIndex = $this->findStartIndex($data);

        if ($selectedIndex) {
            for ($j = $selectedIndex; $j <= count($data); $j += 2) {
                if (!isset($data[$j]) || $data[$j] === 'Annual Reports') {
                    break;
                }

                $members[] = [
                    'member_title' => isset($data[$j]) ? trim(preg_replace('/^Title\s*/', '', $data[$j])) : '',
                    'member_address' => $data[$j + 1] ?? '',
                ];
            }
        }

        return $this->combineNamesWithMembers($membersNames, $members);
    }

    /**
     * Find start index for member data extraction
     */
    private function findStartIndex($data)
    {
        $officerIndex = array_search('Officer/Director Detail', $data);
        if ($officerIndex === false) {
            $officerIndex = array_search('Authorized Person(s) Detail', $data);
        }
        $nameAddrIndex = array_search('Name & Address', $data);

        return ($officerIndex !== false && $nameAddrIndex !== false) ? $officerIndex + 2 : 0;
    }

    /**
     * Combine names with members array
     */
    private function combineNamesWithMembers($names, $members)
    {
        if (count($members) === 0 || count($names) !== count($members)) {
            return [];
        }

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
