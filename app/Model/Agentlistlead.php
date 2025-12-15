<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agentlistlead extends Model
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'agentlist_id', 'leads_id', 'business_name', 'business_city', 'business_county', 'business_county_id', 'business_unit_count',
		'business_contact_name', 'business_contact_number', 'last_called', 'times_called', 'stattus', 'agent_id'
	];

	// get date and integer columns
	public function scopeGet_column_type()
	{
		//get integer columns
		$tableheading  = Schema::getColumnListing('agentlistleads'); //get columns name
		$columns = array();
		foreach ($tableheading as $head) {
			$type = DB::connection()->getDoctrineColumn('agentlistleads', $head)->getType()->getName(); //get column type
			if ($type == 'bigint' && $head != 'id' || $type == 'decimal' && $head != 'id') { //if it is integer or decimal, except the id table
				$columns['number'][] = $head;
			} else if ($type == 'date') { //if it is integer or decimal, except the id table
				$columns['date'][] = $head;
			} else {
				$columns['other'][] = $head;
			}
		}
		// Adding distance manually
		array_push($columns['number'], 'distance');
		return $columns;
	}

	public function agentLists()
	{
		return $this->belongsToMany(AgentList::class);
	}
}
