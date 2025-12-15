<?php

namespace App\Model;

use App\Model\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends BaseModel
{

	use HasFactory, SoftDeletes;

	protected $table = 'templates';

	protected $fillable = ['template_name', 'template_name_slug', 'template_type', 'template_subject', 'template_content', 'set_for_all', 'created_by'];
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function getDataByModel($options = [])
	{
		$query = $this->newQuery();

		$query->select('templates.id', 'templates.template_name', 'templates.template_content', 'users.name as user_name')
			->join('users', 'users.id', '=', 'templates.user_id');

		// Handle search
		if (!empty($options['search'])) {
			$keyword = $options['search'];
			$query->where(function ($query) use ($keyword) {
				// foreach ($options['search'] as $value) {
				$query->orWhere(function ($query) use ($keyword) {
					foreach ($this->fillable as $column) {
						$query->orWhere($column, 'like', '%' . $keyword . '%');
					}
				});
				// }
			});
		}

		// Handle pagination
		$perPage = isset($options['perPage']) ? $options['perPage'] : 10;
		$page = isset($options['page']) ? $options['page'] : 1;
		$query->paginate($perPage, ['*'], 'page', $page);

		// Handle sorting
		if (!empty($options['sortBy'])) {
			foreach ($options['sortBy'] as $column => $direction) {
				$query->orderBy($column, $direction);
			}
		}

		return $query->paginate($options['perPage'], ['*'], 'page', $options['page']);
	}

	// Define relationships if needed
	public function userTemplates()
	{
		return $this->hasMany(UserTemplate::class, 'template_id', 'id');
	}
	public function user()
    {
		return $this->belongsToMany(User::class, 'user_templates');
    }
	
}
