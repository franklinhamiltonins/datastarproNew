<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FhinsureLog extends BaseModel
{
    use HasFactory, SoftDeletes;
    protected $table = 'newsletters';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'zip',
        'insurance_type',
        'is_checked',
        'site_name',
        'profile_add_status',
        'profile_add_response',
        'list_add_status',
        'list_add_response',
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
