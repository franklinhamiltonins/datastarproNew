<?php

namespace App\Model\LeadsModel\LeadTrait;

use App\Model\User;
use App\Model\Campaign;
use App\Model\LeadSource;
use App\Model\LeadAsanaDetail;
use App\Model\ContactStatus;
use App\Model\Dialing;
use App\Model\File;
use App\Model\LeadAdditionalPolicy;
use App\Model\InsuranceType;

use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\ContactScrap;
use App\Model\LeadsModel\Log;
use App\Model\LeadsModel\Note;
use App\Model\LeadsModel\Action;

/**
 * Trait for relationship methods in Lead model.
 * Contains all carrier, rating, and other relationship definitions.
 */
trait LeadRelationshipsTraitOne
{
    // ==================== Other Relationships ====================

    /**
     * Get the contact scraps for this lead.
     */
    public function contactscraps()
    {
        return $this->hasMany(ContactScrap::class, 'lead_id');
    }

    /**
     * Get the contacts for this lead.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the logs for this lead.
     */
    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Get the notes for this lead.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the insurances for this lead.
     */
    public function insurances()
    {
        return $this->hasMany(InsuranceType::class);
    }

     /**
     * Get the files for this lead.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'uploaded_files');
    }

    /**
     * Get the actions for this lead.
     */
    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    /**
     * Get the additional policies for this lead.
     */
    public function leadAdditionalpolicy()
    {
        return $this->hasMany(LeadAdditionalPolicy::class);
    }

    /**
     * Get the collaborators (users) for this lead.
     */
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'collaborators', 'lead_id', 'user_id');
    }

    /**
     * Get the campaigns for this lead.
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaigns_leads');
    }

    /**
     * Get the Asana detail for this lead.
     */
    public function leadAsanaDetail()
    {
        return $this->hasOne(LeadAsanaDetail::class, 'lead_id', 'id');
    }

    /**
     * Get the assigned user for this lead.
     */
    public function assignedUser()
    {
        return $this->hasOne(User::class, 'id', 'assigned_user_id');
    }

    /**
     * Get custom user with special handling for service team.
     */
    public function customUserGetting()
    {
        if ($this->assigned_user_id && $this->assigned_user_id == -1) {
            return (object) [
                'id' => -1,
                'name' => 'Service Team',
                'email' => '',
                'laravel_through_key' => null,
            ];
        }

        return $this->assigned_user;
    }

    /**
     * Get the dialings for this lead.
     */
    public function dialings()
    {
        return $this->belongsToMany(Dialing::class, 'dialings_leads');
    }

    /**
     * Get the lead status.
     */
    public function leadStatus()
    {
        return $this->hasOne(ContactStatus::class, 'id', 'pipeline_status_id');
    }

    /**
     * Get the owning agent.
     */
    public function ownedAgent()
    {
        return $this->hasOne(User::class, 'id', 'pipeline_agent_id');
    }

    /**
     * Get lead source relationship.
     */
    public function leadSource()
    {
        return $this->hasOne(LeadSource::class, 'id', 'lead_source');
    }
}
