<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ContactsCurlApiSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('scrap_api_platforms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('platform_name', 255);
            $table->string('api_key', 255)->nullable();
            $table->string('api_username', 255)->nullable();
            $table->unsignedTinyInteger('priority_order');
            $table->tinyInteger('status');
            $table->string('api_auth_url', 255)->nullable();
            $table->string('api_contact_search_url', 255)->nullable();
            $table->string('api_auth_token', 255)->nullable();
            $table->string('platform_type', 255);
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
        Schema::dropIfExists('scrap_api_platforms');
    }
}
