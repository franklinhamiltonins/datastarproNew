<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactsTableIndexAndTableForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            //drop foreign pivot table 
            Schema::dropIfExists('leads_contacts');
            
            $table->unsignedBigInteger('lead_id')->nullable()->change(); //change lead_id type

            //foreign Key
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->index(['c_first_name','c_last_name']);
            $table->index(['c_phone']);
            $table->index(['c_first_name','c_last_name','c_address1']);

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['c_first_name','c_last_name']);
            $table->dropIndex(['c_phone']);
            $table->dropIndex(['c_first_name','c_last_name','c_address1']);
        });
    }
}
