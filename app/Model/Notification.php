<?php

namespace App\Model;

use App\Model\BaseModel;

class Notification extends BaseModel
{
	protected $table = 'smsnotifications';
	protected $fillable = ['smscontent', 'user_id', 'contact_id', 'status'];
	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];
	protected $dates = ['deleted_at'];
	public function getDataByModel($options = [])
	{
		$query = $this->newQuery();

		$query->select('smsnotifications.id', 'smsnotifications.smscontent', 'contacts.c_full_name as contact_name', 'users.name as user_name')
			->join('contacts', 'contacts.id', '=', 'smsnotifications.contact_id')
			->join('users', 'users.id', '=', 'smsnotifications.user_id');

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
}
