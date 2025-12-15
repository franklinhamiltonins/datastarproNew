<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_actions', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('action_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
           $table->foreign('action_id')->references('id')->on('actions')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['lead_id','action_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_actions');
    }
}
