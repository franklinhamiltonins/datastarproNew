<?php

namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as OriginalRole;

class Role extends OriginalRole
{
    use SoftDeletes;
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}