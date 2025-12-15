<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmtpConfiguration extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_name',
        'auth',
        'user_id',
        'signature_image',
        'signature_text',
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

    public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function provider()
	{
		return $this->belongsTo(EmailProvider::class);
	}
    
}
