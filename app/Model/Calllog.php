<?php

namespace App\Model\BaseModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calllog extends BaseModel
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'user_id', 'lead_id', 'status', 'notes', 'contact_id'
	];
}
