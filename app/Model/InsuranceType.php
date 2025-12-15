<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Carrier;
use App\Model\Rating;

class InsuranceType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insurance_types'; 

    protected $fillable = ['name','status'];

    protected $dates = ['deleted_at'];

    public function carriers()
    {
        return $this->belongsToMany(Carrier::class, 'carrier_insurance_type');
    }

    public function ratings()
    {
        return $this->belongsToMany(Rating::class, 'rating_insurance_type');
    }

}
