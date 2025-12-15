<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScrapCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrap_cities', function (Blueprint $table) {
            $table->id();
            //add Current Insurances columns
            $table->string('search_keyword', 255)->nullable();
            $table->string('city', 255);
            $table->string('state', 255);
            $table->string('state_code', 255);
            $table->tinyInteger('status');
            $table->tinyInteger('county_id')->nullable();
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
        Schema::dropIfExists('scrap_cities');
    }
}
