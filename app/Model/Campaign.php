<?php


namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  App\Model\LeadsModel\Lead;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name','status','campaign_date','type','size','status','lead_number','lead_ids', 'lead_actions'
    ];

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'campaigns_leads');
    }
    // public function files()
    // {

    //     return $this->morphMany(File::class, 'uploaded_files');
    // }
    public function files()
    {
        return $this->morphMany(File::class, 'uploaded_files');
    }

    public function actions()
    {
        return $this->hasMany('App\Model\LeadsModel\Action', 'id');
    }

}


