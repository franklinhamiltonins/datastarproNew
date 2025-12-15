<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
             $table->bigIncrements('id');
            $table->bigInteger('lead_id')->nullable();
            $table->string('c_first_name');
            $table->string('c_last_name');
            $table->string('c_title')->nullable();
            $table->string('c_address1');
            $table->string('c_address2')->nullable();
            $table->string('c_city');
            $table->string('c_state');
            $table->string('c_zip');  
            $table->string('c_county');
            $table->string('c_phone')->nullable();
            $table->string('c_email')->nullable();
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
        Schema::dropIfExists('contacts');
    }
}
