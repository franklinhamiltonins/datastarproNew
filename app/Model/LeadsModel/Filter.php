<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filter extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id', 'is_business_name', 'name', 'type', 'address', 'business_name', 'business_id', 'operator', 'distance', 'latitude', 'longitude', 'conditions'
    ];

    public function scopeGetFilters()
    {
        $filter = Filter::whereNull('deleted_at')->get();
        return $filter ? $filter : null;
    }
}
