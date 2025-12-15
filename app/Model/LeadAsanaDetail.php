<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Model\LeadsModel\Lead;

class LeadAsanaDetail extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'lead_id',
        'appraisal',
        'wind_mitigation',
        'loss_run_authorization',
        'inspection_contact_form',
        'sov_form',
        'accord_form',
        'property_bind_coverage',
        'general_liability_bind_coverage',
        'do_bind_coverage',
        'legal_defense_bind_coverage',
        'umbrella_bind_coverage',
        'crime_bind_coverage',
        'workers_comp_bind_coverage',
        'flood_bind_coverage',
        'sent_to_client',
        'meeting_with_client',
        'signed_docusign_received',
        'add_policies_to_epic',
        'send_invoices_to_accounting',
        'add_policies_to_bind_document',
        'add_policies_to_eoi_direct',
        'send_policies_to_insured',
        'down_payment',
        'financing',
        'property_payment',
        'general_liability_payment',
        'do_payment',
        'legal_defense_payment',
        'umbrella_payment',
        'crime_payment',
        'workers_comp_payment',
        'flood_payment',
        'status',
        'asana_stage'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'lead_asana_details';

    public $timestamps = true;

    public function setCreatedAtAttribute($value)
    {
        if ($value) {
            $this->attributes['created_at'] = Carbon::parse($value)->setTimezone('UTC');
        }
    }

    public function setUpdatedAtAttribute($value)
    {
        if ($value) {
            $this->attributes['updated_at'] = Carbon::parse($value)->setTimezone('UTC');
        }
    }

    // Belongs to a Lead
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
    }


}
