<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carriers';

    protected $fillable = ['name', 'status'];

    protected $dates = ['deleted_at'];

    public function insuranceTypes()
    {
        return $this->belongsToMany(InsuranceType::class, 'carrier_insurance_type');
    }

    public static function leadFieldWithNickName()
    {
        return [
            'ins_prop_carrier' => 'Property',
            'general_liability' => 'General Liability',
            'crime_insurance' => 'Crime Insurance',
            'directors_officers' => 'Directors & Officers',
            'umbrella_exclusions' => 'Umbrella',
            'workers_compensation' => 'Workers Compensation',
            'flood' => 'Flood',
            'difference_in_condition' => 'Difference In Conditions',
            'x_wind' => 'X-Wind',
            'equipment_breakdown' => 'Equipment Breakdown',
            'commercial_automobiles' => 'Commercial AutoMobile',
            'marina' => 'Marina',
        ];
    }
}
