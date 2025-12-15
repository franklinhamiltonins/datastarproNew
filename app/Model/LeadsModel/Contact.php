<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use App\Model\ContactStatus;
use App\Model\User;
use DB;

class Contact extends Model
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'lead_id',
		'c_full_name',
		'c_first_name',
		'c_last_name',
		'c_title',
		'c_address1',
		'c_address2',
		'c_city',
		'c_state',
		'c_zip',
		'c_county',
		'c_phone',
		'c_email',
		'c_is_client',
		'c_status',
		'agent_call_initiated',
		'has_initiated_stop_chat',
		'c_merge_status',
		'contact_slug',
		'added_by_scrap_apis',
		'prospect_verified', 
		'is_updated',
		'current_sent_smsprovider_id',
		'first_sms_date_time',
		'next_sms_date_time',
		'respond_to_cron_flag',
		'skip_response_step',
		'klaviyo_call_initiated',
		'c_agent_id',
		'archive_sms',
		'fake_address',
		'new_scrap_status'
	];


	/**
	 * App\Lead relationship
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasOne
	 */
	// make connection with users table , due to foreign key
	public function leads()
	{
		return $this->belongsTo(Lead::class, 'lead_id');
	}

	public function notes()
	{
		return $this->hasMany(Note::class);
	}

	public function contactStatus()
    {
        return $this->hasOne(ContactStatus::class, 'id', 'c_status');
    }

    public function assignedAgent()
    {
        return $this->hasOne(User::class, 'id', 'c_agent_id');
    }

	public function scopeNotClient($q)
	{
		return $q->where('c_is_client', 0);
	}

	public static function updateContactsWithScrapData($data, $id)
	{
		// $data['prospect_verified'] = 'merged';
		return DB::table('contacts')
			->where('id', $id)
			->update($data);
	}

	public static function storeSocialProfileData($social_profile_data)
	{
		// Define the default structure with all possible fields set to null
		$default_data = [
			'contact_id' => null,
			'linkedin_url' => null,
			'linkedin_username' => null,
			'linkedin_id' => null,
			'facebook_username' => null,
			'facebook_id' => null,
			'facebook_url' => null,
			'twitter_url' => null,
			'twitter_username' => null,
			'github_url' => null,
			'github_username' => null
		];

		// Filter and normalize each entry to ensure all fields are present
		$normalized_data = array_filter(
			array_map(function ($entry) use ($default_data) {
				if (count($entry) > 1) {
					return array_merge($default_data, $entry);
				}
				return null; // If the condition is not met, return null
			}, $social_profile_data),
			function ($entry) {
				return $entry !== null; // Filter out null entries
			}
		);

		// Perform a bulk insert with the normalized data
		DB::table('scrap_contact_social_profile')->insert($normalized_data);
	}

	// public static function storeContactApiPlatformStatus($status, $contact_id, $api_setting_data)
	// {
	// 	DB::table('scrap_contact_api_platforms')->insert(['contact_id' => $contact_id, 'status' => $status, 'api_platform_id' => $api_setting_data['id'], 'record_name' => $api_setting_data['platform_name'] . '_' . date('y-m-d')]);
	// }
	public static function storeContactApiPlatformStatus($insert_data)
	{
		// $insert_data['created_at'] = date('Y-m-d H:i:s');
		DB::table('scrap_contact_api_platforms')->insert($insert_data);
	}

	public static function checkForCurrentStatus($contact_id, $api_platform_id)
	{
		$res = '';
		// $partial_data_status = DB::table('scrap_contact_api_platforms')->where('contact_id', $contact_id)
		// 	->where('api_platform_id', $api_platform_id)
		// 	->select('status')
		// 	->orderBy('id', 'desc')
		// 	->toSql();
		$partial_data_status = DB::table('contacts')->where('id', $contact_id)
			->select('prospect_verified')->first();
		// dd($partial_data_status);
		if ($partial_data_status > 0)
			$res = $partial_data_status->prospect_verified;
		else
			$res = 'unavailable';
		// dd($res);
		return $res;
	}
}
