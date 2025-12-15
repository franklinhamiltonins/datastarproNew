<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use App\Model\LeadsModel\Lead;
use DB;

class LeadSource extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'lead_source'; 

    protected $fillable = ['name','status'];

    protected $dates = ['deleted_at'];
}
