<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ScrapApiPlatform;
use App\Model\LeadsModel\ScrapContactApiPlatform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScrapContactController extends Controller
{
    public function scrapContactView()
    {
        $scrapVars = [];
        $usedApiPlatformId = '';
        $partial_data_response = ScrapContactApiPlatform::checkForPartialData();

        $scrapPartialData = $partial_data_response['partial_data'];

        if (count($scrapPartialData) > 0) {
            $usedApiPlatformId = $scrapPartialData[0]->api_platform_id;
        }
        // All platform setting
        $scrapPlatformSettings = ScrapApiPlatform::getAllPlatformSettings($usedApiPlatformId);
        if (count($scrapPlatformSettings) <= 0) {
            $scrapPlatformSettings = ScrapApiPlatform::getAllPlatformSettings()[0];
        }
        $scrapVars['platform_name'] = $scrapPlatformSettings[0]['platform_name'];
        $scrapVars['limit'] = 2;
        $scrapVars['all_scrap'] = self::allRecords();

        return view('scrap_api_platform.scrap-contact', compact('scrapVars'));
    }

    // Listing view
    public function callForScrapContact(Request $request)
    {
        $limit = $request->post('limit');
        $contact_id = $request->post('contact_id');

        return ScrapContactApiPlatform::callForScrapContactApiPlatform($limit, $contact_id);
    }

    public function allRecords()
    {
        $response = ['success' => [], 'partial' => [], 'unavailable' => [], 'not found' => []];

        $all_record = Contact::where('prospect_verified', '!=', 'pending')
            ->where('added_by_scrap_apis', 1)
            ->with('leads')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($all_record as $record) {
            $response[$record['prospect_verified']][] = $record;
        }

        return $response;
    }

    public function exportCsv($status)
    {
        $filename = 'scrapped_contact-'.time().'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($status) {
            $handle = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($handle, [
                'Sl No',
                'Status',
                'First Name',
                'Last Name',
                'Lead',
                'Email',
                'Phone',
                'Address',
                'City',
                'Zip',
            ]);
            // Fetch and process data in chunks
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
            foreach ($records as $record) {

                // Extract data from each employee.
                $data = [
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
                ];

                // Write data to a CSV file.
                fputcsv($handle, $data);
                // fputcsv($handle, $data);
            }

            // Close CSV file handle
            fclose($handle);
        }, 200, $headers);
    }
}
