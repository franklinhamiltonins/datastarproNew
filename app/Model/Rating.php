<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\InsuranceType;

class Rating extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ratings'; 

    protected $fillable = ['name','status'];

    protected $dates = ['deleted_at'];

    public function insuranceTypes()
    {
        return $this->belongsToMany(InsuranceType::class, 'rating_insurance_type');
    }
}
