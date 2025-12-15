<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Carrier;

class LeadAdditionalPolicy extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'lead_id',
        'carrier',
        'policy_type',
        'expiry_premium',
        'policy_renewal_date',
        'hurricane_deductible',
        'all_other_perils',
        'insurance_coverage'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'leads_additional_policy';

    public function listCarrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier');
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->setTimezone('UTC');
    }

    public function setUpdatedAtAttribute($value)
    {
        $this->attributes['updated_at'] = Carbon::parse($value)->setTimezone('UTC');
    }
}
