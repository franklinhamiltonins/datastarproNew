<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailFetchedLog extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "mail_fetched_log";

    protected $fillable = ['date', 'time', 'type', 'file_path','status'];
    protected $dates = ['deleted_at'];
}
