<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\InsuranceType;

class Carrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carriers'; 

    protected $fillable = ['name','status'];

    protected $dates = ['deleted_at'];

    public function insuranceTypes()
    {
        return $this->belongsToMany(InsuranceType::class, 'carrier_insurance_type');
    }

}
