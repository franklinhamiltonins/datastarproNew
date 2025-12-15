<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('contact_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
           $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['lead_id','contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_contacts');
    }
}
