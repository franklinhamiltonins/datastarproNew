<?php


namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  App\Model\LeadsModel\Lead;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapCounty extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
       'name','status','created_at','updated_at','deleted_at'
    ];

    public function scrapCities()
    {
        return $this->hasMany(ScrapCity::class, 'scrap_city');
    }
    

}


