<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Smsnotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'smscontent',
        'user_id',
        'contact_id',
        'status',
    ];
}