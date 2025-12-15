<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dialing extends  BaseModel
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];
	protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'lead_number', 'user_id', 'concurrent_user'];

	protected $fillable = [
		'name', 'lead_number', 'status', 'user_id', 'referral_marker'
	];

	// public function agentListLeads()
	// {
	// 	return $this->belongsToMany(AgentListLead::class);
	// }



	public function leads()
	{
		return $this->belongsToMany(\App\Model\LeadsModel\Lead::class, 'dialings_leads');
	}

	public function users()
	{
		return $this->belongsToMany(User::class);
	}
}
