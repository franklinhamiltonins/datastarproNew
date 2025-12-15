<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadAssignmentLog extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'lead_assignment_logs';

    protected $fillable = [
        'status_id',
        'agent_id',
        'assigned_user_id',
        'lead_id',
        'changed_by_user_id',
    ];

    protected $dates = ['created_at','updated_at','deleted_at'];
}
