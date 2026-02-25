<?php

namespace App\Model\LeadsModel;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapContactApiPlatform extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'contact_id',
        'api_platform_id',
        'status',
        'record_name',
        'api_response',
    ];

    /**
     * App\Lead relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\hasOne
     */
    // make connection with users table , due to foreign key
    public function contacts()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function apiPlatform()
    {
        return $this->belongsTo(ScrapApiPlatform::class, 'api_platform_id');
    }

    public static function callForScrapContactApiPlatform($limit = '', $contactId = '')
    {
        try {
            $partialDataResponse = ScrapContactApiPlatform::checkForPartialData($contactId, $limit, 'call_for_api');
            $scrapPartialData = $partialDataResponse['partial_data'];

            $usedApiPlatformId = '';
            $contactIdArr = [];
            if (count($scrapPartialData) > 0) {
                $usedApiPlatformId = $scrapPartialData[0]->api_platform_id;
                $contactIdArr = $partialDataResponse['contact_ids'];
            } else {
                $contactIdArr = $contactId;
            }

            // All platform setting
            $scrapPlatformSettings = ScrapApiPlatform::getAllPlatformSettings($usedApiPlatformId);

            $contactDataForScrapping = ScrapContactApiPlatform::getContactDataForScrapping($limit, $contactIdArr);

            if (count($contactDataForScrapping) > 0 && ! empty($scrapPlatformSettings)) {
                $scrapPlatformSettings = $scrapPlatformSettings[0];
                switch ($scrapPlatformSettings['platform_name']) {
                    case 'DataLabs':
                        return ScrapDataLabs::callForDataLabsApi($contactDataForScrapping, $scrapPlatformSettings);
                        break;
                    case 'PeopleSearch':
                        return ScrapOpenPeopleSearch::callForOpenPeopleSearchApi($contactDataForScrapping, $scrapPlatformSettings);
                        break;
                    default:
                        return 'not found';
                        // code block
                }
            }

            return ['status' => 'true', 'data' => [], 'message' => 'No contacts available'];
        } catch (\Throwable $err) {
            toastr()->error($err);
            throw $err;
        }
    }

    // Get Contact Data For Scrapping
    public static function getContactDataForScrapping($limit, $contactIdArr = [])
    {
        if (! empty($contactIdArr) && count($contactIdArr) > 0) {
            return Contact::whereIn('id', $contactIdArr)->whereIn('prospect_verified', ['pending', 'partial', 'unavailable'])->where('added_by_scrap_apis', 1)->orderBy('id', 'asc')->with('leads')->limit($limit)
                ->get();
        } else {
            return Contact::where('prospect_verified', 'pending')->where('added_by_scrap_apis', 1)->orderBy('id', 'asc')->with('leads')->limit($limit)
                // ->toSql();
                ->get();
        }
    }

    public static function checkForPartialData($contactId = '', $limit = '', $type = '')
    {
        $contactIdArr = [];
        $response = [];
        $partialDataQuery = DB::table('scrap_contact_api_platforms')->where('merged_status', 'pending');
        if ($contactId != '') {
            $partialDataQuery = $partialDataQuery->where('contact_id', $contactId)->whereIn('status', ['partial', 'unavailable']);
        } else {
            $partialDataQuery = $partialDataQuery->whereIn('status', ['partial', 'unavailable']);
        }
        if ($limit != '') {
            $partialDataQuery->limit($limit);
        }
        $partialData = $partialDataQuery->get();

        if (count($partialData) > 0) {
            foreach ($partialData as $key => $val) {
                array_push($contactIdArr, $val->contact_id);
                if ($type == 'call_for_api') {
                    // All platform setting
                    $scrapPlatformSettings = ScrapApiPlatform::getAllPlatformSettings($partialData[0]->api_platform_id);
                    if (! empty($scrapPlatformSettings)) {
                        DB::table('scrap_contact_api_platforms')->where('id', $val->id)->update(['merged_status' => 'processed_next']);
                    }
                }
            }
            $response['contact_ids'] = $contactIdArr;
        }
        $response['partial_data'] = $partialData;

        return $response;
    }
}
