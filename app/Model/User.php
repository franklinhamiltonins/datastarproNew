<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\AgentLog;
use App\Model\LeadAsanaDetail;
use App\Model\LeadsModel\Lead;

class User extends Authenticatable
{
	use HasFactory, Notifiable, HasRoles, SoftDeletes;
	protected $guard_name = 'web';


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
		'bigoceanuser_id',
		'twofactor_authentication'
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public function lastCalledContacts()
	{
		$attributes = parent::toArray();
		if (!auth()->user()->can('agent-create')) :
			$attributes['contact_ids'] = AgentLog::where('status', 'call_initiated')->where('user_id', auth()->user()->id)->get();
		endif;



		return $attributes;
	}

	public function logs()
	{

		return $this->hasMany(Log::class);
	}
	public function actions()
	{

		return $this->hasMany(Action::class);
	}

	public function dialings()
	{
		return $this->belongsToMany(Dialing::class);
	}

	public function smtp()
	{

		return $this->hasOne(SmtpConfiguration::class);
	}

	public function userTemp()
	{
		return $this->hasMany(UserTemplate::class);
	}

	public function collaboratingLeads()
	{
	    return $this->belongsToMany(Lead::class, 'collaborators', 'user_id', 'lead_id');
	}

	public function accessibleUsers()
    {
        return $this->belongsToMany(
            User::class,
            'agent_user_access',
            'agent_id',             // local key
            'accessible_user_id'    // related key
        );
    }

    public function managerTeamList()
    {
        return $this->belongsToMany(
            User::class,
            'manager_team',
            'manager_id',    // local key
            'user_id'    // related key
        );
    }

    public function managers()
	{
	    return $this->belongsToMany(
	        User::class,
	        'manager_team',
	        'user_id',     // agent column
	        'manager_id'   // manager column
	    );
	}

    public function assignedLeadAsanaDetails()
    {
        return $this->hasMany(Lead::class, 'assigned_user_id', 'id');
    }

}
