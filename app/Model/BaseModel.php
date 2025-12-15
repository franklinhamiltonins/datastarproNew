<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BaseModel extends Model
{
	use HasFactory, SoftDeletes;

	public function getTableName()
	{
		return $this->getTable();
	}

	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	public function getTableHeaders(): array
	{
		$firstRow = $this->first();
		$headers = $firstRow ? array_keys($firstRow->toArray()) : [];
		return array_map(function ($header) {
			return [
				'columnName' => $header,
				'niceName' => Str::title(str_replace('_', ' ', $header))
			];
		}, $headers);
	}


	public function getDataByModel($options = [])
	{


		$query = $this->newQuery();

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
