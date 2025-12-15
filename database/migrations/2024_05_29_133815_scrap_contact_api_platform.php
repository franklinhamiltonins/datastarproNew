<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ScrapContactApiPlatform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('scrap_contact_api_platforms', function (Blueprint $table) {
            $table->bigInteger('contact_id');
            $table->bigInteger('api_platform_id');
            $table->string('status', 255)->nullable();
            $table->string('record_name', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('scrap_api_platforms');
    }
}
