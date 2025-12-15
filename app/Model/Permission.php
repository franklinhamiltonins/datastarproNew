<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;

class Permission extends BaseModel
{

	protected $table = 'permissions';

	protected $fillable = ['name', 'guard_name', 'page'];


	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
