<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailProvider extends  BaseModel
{
	use HasFactory, SoftDeletes;
    protected $fillable = [
        'provider_name',
        'host',
        'port',
        'encryption',
        'auth',
        'created_by',
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

    public function smtp()
	{

		return $this->Many(SmtpConfiguration::class);
	}
}
