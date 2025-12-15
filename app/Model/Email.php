<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\BaseModel;
use Carbon\Carbon;

class Email extends BaseModel
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'contact_id',
        'subject',
        'content',
        'attachment',
        'created_at',
        'updated_at',
        'newsletter_id',
        'module_name'
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
