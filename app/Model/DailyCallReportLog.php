<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyCallReportLog extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "daily_call_report_log";

    protected $fillable = ['call_type', 'domain', 'user_franklin_id', 'btn','call_begin','time_answer', 'duration', 'remote_number', 'dialed_number','call_id','origin_ip', 'term_ip', 'release_cause','mail_fetched_log_id'];
    protected $dates = ['deleted_at'];
}
