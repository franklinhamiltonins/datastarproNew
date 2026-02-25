<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ratings';

    protected $fillable = ['name', 'status'];

    protected $dates = ['deleted_at'];

    public function insuranceTypes()
    {
        return $this->belongsToMany(InsuranceType::class, 'rating_insurance_type');
    }

    public static function leadFieldWithNickName()
    {
        return [
            'rating' => 'Property Rating',
            'gl_rating' => 'General Liability Rating',
            'ci_rating' => 'Crime Insurance Rating',
            'do_rating' => 'Directors & Officers Rating',
            'umbrella_rating' => 'Umbrella Rating',
            'wc_rating' => 'Workers Compensation Rating',
            'flood_rating' => 'Flood Rating',
        ];
    }
}
