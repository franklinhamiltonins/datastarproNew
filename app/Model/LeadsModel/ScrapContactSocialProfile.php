<?php

namespace App\Model\LeadsModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapContactSocialProfile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'contact_id',
        'linkedin_url',
        'linkedin_username',
        'linkedin_id',
        'facebook_username',
        'facebook_id',
        'facebook_url',
        'twitter_url',
        'twitter_username',
        'github_url',
        'github_username',
    ];
}
