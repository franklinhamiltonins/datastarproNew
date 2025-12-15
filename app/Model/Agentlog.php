<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agentlog extends Model
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'id', 'user_id', 'message', 'created_at', 'status', 'lead_id', 'contact_id'
	];


	public function leads()
	{

		return $this->belongsTo(Lead::class, 'lead_id');
	}
	public function users()
	{

		return $this->belongsTo('App\Model\User', 'user_id');
	}
}
