<?php

namespace App\Model;


use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventLogs extends  BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'contact_id',
        'agent_id',
        'event_id',
        'event_name',
        'event_desc',
        'event_date',
        'status',
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
