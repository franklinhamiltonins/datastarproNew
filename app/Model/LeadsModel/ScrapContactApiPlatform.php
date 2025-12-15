<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use DB;
use Validator;
use Carbon\Carbon;


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
		'api_response'
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


	public static function callForScrapContactApiPlatform($limit = '', $contact_id = '')
	{
		try {
			// $limit = $request->post('limit');
			// $contact_id = $request->post('contact_id');
			// dd($contact_id, $limit);
			$partial_data_response = ScrapContactApiPlatform::checkForPartialData($contact_id, $limit, 'call_for_api');
			// dd($partial_data_response);
			$scrap_partial_data = $partial_data_response['partial_data'];

			$used_api_platform_id = '';
			$contact_ids = [];
			if (count($scrap_partial_data) > 0) {
				$used_api_platform_id = $scrap_partial_data[0]->api_platform_id;
				$contact_ids = $partial_data_response['contact_ids'];
			} else {
				$contact_ids = $contact_id;
			}

			//All platform setting
			$scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($used_api_platform_id);

			$contactDataForScrapping = ScrapContactApiPlatform::getContactDataForScrapping($limit, $contact_ids);

			if (count($contactDataForScrapping) > 0 && !empty($scrap_platform_settings)) {
				$scrap_platform_settings = $scrap_platform_settings[0];
				switch ($scrap_platform_settings['platform_name']) {
					case 'DataLabs':
						$datalabs_resp = ScrapDataLabs::callForDataLabsApi($contactDataForScrapping, $scrap_platform_settings);
						return $datalabs_resp;
						break;
					case 'PeopleSearch':
						$people_search_resp = ScrapOpenPeopleSearch::callForOpenPeopleSearchApi($contactDataForScrapping, $scrap_platform_settings);
						return $people_search_resp;
						break;
					default:
						return 'not found';
						//code block
				}
			}
			return ['status' => 'true', 'data' => [], 'message' => 'No contacts available'];
		} catch (\Throwable $err) {
			toastr()->error($err);
			throw ($err);
		}
	}

	// Get Contact Data For Scrapping
	public static function getContactDataForScrapping($limit, $contact_ids = [])
	{
		if (!empty($contact_ids) && count($contact_ids) > 0) {
			$contact_data = Contact::whereIn('id', $contact_ids)->whereIn('prospect_verified', ['pending', 'partial', 'unavailable'])->where('added_by_scrap_apis', 1)->orderBy('id', 'asc')->with('leads')->limit($limit)
				// ->toSql();
				->get();
		} else {
			$contact_data = Contact::where('prospect_verified', 'pending')->where('added_by_scrap_apis', 1)->orderBy('id', 'asc')->with('leads')->limit($limit)
				// ->toSql();
				->get();
		}
		return $contact_data;
	}

	public static function checkForPartialData($contact_id = '', $limit = '', $type = '')
	{
		$contact_ids = [];
		$response = [];
		$partial_data_query = DB::table('scrap_contact_api_platforms')->where('merged_status', 'pending');
		if ($contact_id != '') {
			$partial_data_query = $partial_data_query->where('contact_id', $contact_id)->whereIn('status', ['partial', 'unavailable']);
		} else {
			$partial_data_query = $partial_data_query->whereIn('status', ['partial', 'unavailable']);
		}
		if ($limit != '')
			$partial_data_query->limit($limit);
		$partial_data = $partial_data_query->get();

		if (count($partial_data) > 0) {
			foreach ($partial_data as $key => $val) {
				array_push($contact_ids, $val->contact_id);
				if ($type == 'call_for_api') {
					// All platform setting
					$scrap_platform_settings = ScrapApiPlatform::getAllPlatformSettings($partial_data[0]->api_platform_id);
					if (!empty($scrap_platform_settings))
						DB::table('scrap_contact_api_platforms')->where('id', $val->id)->update(['merged_status' => 'processed_next']);
				}
			}
			$response['contact_ids'] = $contact_ids;
		}
		$response['partial_data'] = $partial_data;
		return $response;
	}
}
