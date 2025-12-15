<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id','lead_id','user_id','action','contact_name','contact_id','contact_date','campaign_id','created_at'
    ];


    public function leads()
    {

      return $this->belongsTo(Lead::class,'lead_id');
    }
    public function users()
    {

      return $this->belongsTo('App\Model\User','user_id');
    }

    public function campaigns()
    {

      return $this->belongsTo('App\Model\Campaign', 'campaign_id');
    }
}
