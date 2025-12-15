<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns_files', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('file_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
           $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['campaign_id','file_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns_files');
    }
}
