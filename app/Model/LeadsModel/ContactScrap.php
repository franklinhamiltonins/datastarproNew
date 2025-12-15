<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use DB;

class ContactScrap extends Model
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $table = 'contactscraps';
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'lead_id',
		'c_full_name',
		'c_first_name',
		'c_last_name',
		'c_title',
		'c_address1',
		'c_address2',
		'c_city',
		'c_state',
		'c_zip',
		'c_county',
		'c_phone',
		'c_email',
		'c_is_client',
		'c_status',
		'agent_call_initiated',
		'has_initiated_stop_chat',
		'c_merge_status',
		'contact_slug',
		'added_by_scrap_apis',
		'prospect_verified'
	];


	/**
	 * App\Lead relationship
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasOne
	 */
	// make connection with users table , due to foreign key
	public function leads()
	{
		return $this->belongsTo(Lead::class, 'lead_id');
	}
}
