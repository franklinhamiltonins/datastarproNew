<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactStatus extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ["name","false_status","display_in_pipedrive","priority",'special_marker','status_type'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'contact_status';

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->setTimezone('UTC');
    }

    public function setUpdatedAtAttribute($value)
    {
        $this->attributes['updated_at'] = Carbon::parse($value)->setTimezone('UTC');
    }
}
