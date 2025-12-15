<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
  use HasFactory,SoftDeletes;

  /**
   * The attributes that should be mutated to dates.
   *
   * @var array
   */
  protected $dates = ['deleted_at'];
  protected $fillable = [
      'lead_id','user_id','contact_id','title','description'
  ];
    public function leads()
    {
      return $this->belongsTo(Lead::class,'lead_id');
    }

    public function contacts()
    {
      return $this->belongsTo(Contact::class,'contact_id');
    }
}
