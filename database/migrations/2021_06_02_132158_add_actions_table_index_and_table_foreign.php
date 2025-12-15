<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionsTableIndexAndTableForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actions', function (Blueprint $table) {
            //drop foreign pivot table 
            Schema::dropIfExists('leads_actions');
            //add foreign on table
            $table->unsignedBigInteger('lead_id')->nullable()->change();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            //add index
            $table->index(['contact_name']);

          
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->dropIndex(['contact_name']);
        });
    }
}
