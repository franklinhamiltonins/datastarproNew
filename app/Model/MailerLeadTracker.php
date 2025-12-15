<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Model\LeadSource;
use App\Model\User;

class MailerLeadTracker extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "mailer_leads_tracker";

    protected $fillable = [
        'description',
        'created_by',
        'date',
        'lead_source',
        'user_id',
        'contact_firstname',
        'contact_lastname',
        'phone',
        'email_address',
        'contact_address',
        'contact_title',
        'contact_status',
        'business',
        'business_type',
        'business_address',
        'business_city',
        'business_zip',
        'status_note',
        'status',
    ];

    public function leadSource()
    {
        return $this->hasOne(LeadSource::class,'id', 'lead_source');
    }

    public function agent()
    {
        return $this->hasOne(User::class,'id', 'user_id');
    }
}
