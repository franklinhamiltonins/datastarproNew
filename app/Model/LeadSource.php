<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadSource extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'lead_source';

    protected $fillable = ['name', 'status'];

    protected $dates = ['deleted_at'];
}
