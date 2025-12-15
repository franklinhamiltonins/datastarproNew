<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\ActivityReport;

class ActivityReportAor extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "activity_report_aor_details";

    protected $fillable = ['activity_report_id', 'aor', 'aor_community_name', 'aor_effective_date','expiring_aor_premium'];

    protected $dates = ['deleted_at'];

    public function activity()
    {
        return $this->belongsTo(ActivityReport::class,'activity_report_id','id');
    }
}
