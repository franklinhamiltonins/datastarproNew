<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTemplate extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['user_id', 'template_id'];

    public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
    // public function template()
	// {
	// 	return $this->belongsTo(Template::class);
	// }
}

