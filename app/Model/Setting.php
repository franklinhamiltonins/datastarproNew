<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proceed_time_in_minute',
        'notify_email',
        'process_time_in_day_pipeline',
        'notify_email_pipeline',
        'renewal_days_in_pipeline',
        'created_at'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function setCreatedAtAttribute($value)
	{
		$this->attributes['created_at'] = Carbon::parse($value)->setTimezone('UTC');
	}

	public function setUpdatedAtAttribute($value)
	{
		$this->attributes['updated_at'] = Carbon::parse($value)->setTimezone('UTC');
	}
}