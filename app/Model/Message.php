<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;

class Message extends BaseModel
{

	protected $table = 'messages';

	protected $fillable = ['user_id', 'contact_id', 'content', 'chat_type', 'chat_sms_sent_status', 'created_at', 'newsletter_id', 'through_sms_provider_flag', 'msg_type', 'max_time_to_send'];
	// protected $fillable = ['user_id', 'contact_id', 'content', 'chat_type'];

	// protected $casts = [
	// 	'created_at' => 'datetime',
	// 	'updated_at' => 'datetime',
	// ];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function setCreatedAtAttribute($value)
	{
		$this->attributes['created_at'] = Carbon::parse($value)->setTimezone('UTC');
	}

	public function setUpdatedAtAttribute($value)
	{
		$this->attributes['updated_at'] = Carbon::parse($value)->setTimezone('UTC');
	}
	// Define relationships if needed
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class);
	}

	public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}