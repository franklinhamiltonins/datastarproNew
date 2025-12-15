<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\ActivityReportFile;
use App\Model\ActivityReportAor;
use App\Model\User;
use App\Model\LeadsModel\Lead;

class ActivityReport extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "activity_reports";

    protected $fillable = [
        'created_by',
        'user_id',
        'date',
        'appointments',
        'policies',
        'community_name',
        'community_id',
        'expiry_policies_premium',
        'aor_breakdown',
    ];

    protected $dates = ['deleted_at'];

    public function files()
    {
        return $this->hasMany(ActivityReportFile::class,'activity_report_id','id');
    }

    public function aor()
    {
        return $this->hasMany(ActivityReportAor::class,'activity_report_id','id');
    }

    public function agent()
    {
        return $this->hasOne(User::class,'id', 'user_id');
    }

    public function leads()
    {
        return $this->hasOne(Lead::class,'id', 'community_id');
    }
}
