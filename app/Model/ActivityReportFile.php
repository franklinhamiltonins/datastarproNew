<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\ActivityReport;

class ActivityReportFile extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "activity_report_files";

    protected $fillable = ['activity_report_id', 'file_path', 'original_name', 'mime_type'];

    protected $dates = ['deleted_at'];

    public function activity()
    {
        return $this->belongsTo(ActivityReport::class,'activity_report_id','id');
    }
}
