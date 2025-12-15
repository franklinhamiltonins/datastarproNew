<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoginOtpTracker extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "login_otp_tracker";

    protected $fillable = [
        'user_id',
        'otp',
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
