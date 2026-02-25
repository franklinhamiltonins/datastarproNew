<?php

namespace App\Model\LeadsModel;

use App\Model\ContactStatus;
use App\Model\User;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'new_scrap_status',
    ];

    public static function rules()
    {
        return [
            'c_first_name' => 'required|string|max:191',
            'c_last_name' => 'required|string|max:191',
            'c_title' => 'nullable|string|max:191',
            'c_address1' => 'required|string|max:191|regex:/^\d.*/',
            'c_address2' => 'nullable|string|max:191',
            'c_city' => 'nullable|string|max:191',
            'c_state' => 'nullable|string|max:191',
            'c_county' => 'nullable|string|max:191',
            'c_zip' => 'nullable|max:5|string',
            'c_phone' => 'nullable',
            'c_email' => 'nullable|email|max:191',
        ];
    }

    public static function niceNames()
    {
        return [
            'c_first_name' => 'First Name',
            'c_last_name' => 'Last Name',
            'c_address1' => 'Address 1',
            'c_address2' => 'Address 2',
            'c_city' => 'City',
            'c_state' => 'State',
            'c_zip' => 'Zip',
            'c_county' => 'County',
            'c_phone' => 'Phone',
            'c_email' => 'Email',
        ];
    }

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
        return DB::table('contacts')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Get default data structure for social profile
     */
    protected static function getDefaultSocialProfileData(): array
    {
        return [
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
            'github_username' => null,
        ];
    }

    /**
     * Normalize social profile entry
     */
    protected static function normalizeSocialProfileEntry(array $entry, array $defaultData): ?array
    {
        if (count($entry) > 1) {
            return array_merge($defaultData, $entry);
        }

        return null;
    }

    /**
     * Filter valid social profile entries
     */
    protected static function filterValidSocialProfileEntries(array $entries): array
    {
        return array_filter($entries, function ($entry) {
            return $entry !== null;
        });
    }

    /**
     * Store social profile data
     */
    public static function storeSocialProfileData($social_profile_data)
    {
        $defaultData = self::getDefaultSocialProfileData();

        $normalizedData = array_map(function ($entry) use ($defaultData) {
            return self::normalizeSocialProfileEntry($entry, $defaultData);
        }, $social_profile_data);

        $validEntries = self::filterValidSocialProfileEntries($normalizedData);

        DB::table('scrap_contact_social_profile')->insert($validEntries);
    }

    public static function storeContactApiPlatformStatus($insert_data)
    {
        DB::table('scrap_contact_api_platforms')->insert($insert_data);
    }

    public static function checkForCurrentStatus($contact_id, $api_platform_id)
    {
        $res = '';

        $partial_data_status = DB::table('contacts')->where('id', $contact_id)
            ->select('prospect_verified')->first();
        if ($partial_data_status > 0) {
            $res = $partial_data_status->prospect_verified;
        } else {
            $res = 'unavailable';
        }

        return $res;
    }
}
