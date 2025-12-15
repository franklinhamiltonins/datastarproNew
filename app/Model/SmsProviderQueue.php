<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsProviderQueue extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'sms_sent_flag',
        'sms_provider_id',
        'day_delay',
        'created_at'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'sms_provider_queue';

    public function setCreatedAtAttribute($value)
	{
		$this->attributes['created_at'] = Carbon::parse($value)->setTimezone('UTC');
	}

	public function setUpdatedAtAttribute($value)
	{
		$this->attributes['updated_at'] = Carbon::parse($value)->setTimezone('UTC');
	}

}
