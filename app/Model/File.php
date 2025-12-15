<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'file_path',
        'description'
    ];

    // public function leads()
    // {
      
    //   return $this->belongsTo(Lead::class,'lead_id');
    // }

    public function uploaded_files()
    {
        return $this->morphTo();
    }
    
}
