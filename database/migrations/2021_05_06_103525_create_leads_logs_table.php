<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('log_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
           $table->foreign('log_id')->references('id')->on('logs')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['lead_id','log_id']);
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_logs');
    }
}
