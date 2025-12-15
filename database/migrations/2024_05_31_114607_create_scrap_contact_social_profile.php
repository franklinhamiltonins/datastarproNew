<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScrapContactSocialProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrap_contact_social_profile', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('contact_id');
            $table->string('linkedin_url', 255)->nullable();
            $table->string('linkedin_username', 255)->nullable();
            $table->string('linkedin_id', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('facebook_username', 255)->nullable();
            $table->string('facebook_id', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->string('twitter_username', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('github_username', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scrap_contact_social_profile');
    }
}
