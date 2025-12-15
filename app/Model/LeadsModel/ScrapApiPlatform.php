<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use DB;
use Validator;
use Carbon\Carbon;


class ScrapApiPlatform extends Model
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'platform_name',
		'api_key',
		'api_username',
		'priority_order',
		'status',
		'api_auth_url',
		'api_contact_search_url',
		'api_auth_token',
		'platform_type',
		'auth_expiry_date',
		'auth_token_required'
	];


	/**
	 * App\Lead relationship
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasOne
	 */
	// make connection with users table , due to foreign key
	// public function leads()
	// {
	// 	return $this->belongsTo(Lead::class, 'lead_id');
	// }

	/**
	 * Set Priority Order
	 *
	 */
	public static function setPriorityOrder($api_id, $new_order)
	{
		// $api_id = 36;
		$scrapApi = ScrapApiPlatform::withTrashed()->find($api_id);
		if ($scrapApi) {
			$oldOrderNumber = $scrapApi->priority_order;

			if ($new_order > $oldOrderNumber) {
				ScrapApiPlatform::withTrashed()->whereBetween('priority_order', [$oldOrderNumber + 1, $new_order])
					->where('id', '!=', $api_id)
					->decrement('priority_order');
			}
			if ($new_order < $oldOrderNumber) {
				// ScrapApiPlatform::withTrashed()->whereBetween('priority_order', [$new_order, $oldOrderNumber - 1])
				// 	->where('id', '!=', $api_id)
				// 	->increment('priority_order');
				DB::table('scrap_api_platforms')
					->whereBetween('priority_order', [$new_order, $oldOrderNumber])
					->where('id', '!=', $api_id)
					// ->withTrashed()  // Includes soft-deleted records
					->update([
						'priority_order' => DB::raw('priority_order + 1'),
						'updated_at' => Carbon::now()->toDateTimeString(),
					]);
			}

			$scrapApi->priority_order = $new_order;
			$scrapApi->update();
		}
	}

	//formValidation
	public static function formValidation($request_data)
	{
		$rules = [
			'platform_name' => 'string|max:255',
			'api_key' => 'nullable|string|max:255',
			'api_username' => 'nullable|string|max:255',
			'priority_order' => 'required|max:9999|integer',
			'api_auth_url' => 'nullable|string|max:255',
			'api_contact_search_url' => 'nullable|string|max:255',
			'api_auth_token' => 'nullable|string|max:255',
			'platform_type' => 'nullable|string|max:255',
			'status' => 'nullable|string|max:255',
		];
		$niceNames = [
			'platform_name' => 'Platform Name',
			'api_key' => 'Api Key',
			'api_username' => 'Api Username',
			'priority_order' => 'Priority Order',
			'status' => 'Status',
			'api_auth_url' => 'Api Auth Url',
			'api_contact_search_url' => 'Api Contact Search Url',
			'api_auth_token' => 'Api Auth Token',
			'platform_type' => 'Platform Type',
		];


		return Validator::make($request_data, $rules, [], $niceNames);
	}

	//priority validation
	public static function priorityValidation($order)
	{
		$curr_max_order = ScrapApiPlatform::withTrashed()->max('priority_order');

		return [
			'status' => ($order > $curr_max_order + 1) ? false : true,
			'data' => $curr_max_order + 1
		];
	}

	//Get All Platform Setting Data By Asc Order
	public static function getAllPlatformSettings($used_api_platform_id = '')
	{

		if ($used_api_platform_id != '') {
			$used_api_platform_data =
				ScrapApiPlatform::where('id',  $used_api_platform_id)->select('priority_order')->get()[0];

			$platform_data = ScrapApiPlatform::where([['status', '1'], ['id', '!=', $used_api_platform_id], ['priority_order', '>', $used_api_platform_data->priority_order]])->orderBy('priority_order', 'asc')->limit(1)->get();
		} else
			$platform_data = ScrapApiPlatform::where('status', '1')->orderBy('priority_order', 'asc')->limit(1)->get();

		return $platform_data;
	}
}
