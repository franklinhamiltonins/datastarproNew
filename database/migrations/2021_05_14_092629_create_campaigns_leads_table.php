<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns_leads', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('lead_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
           $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['campaign_id','lead_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns_leads');
    }
}
