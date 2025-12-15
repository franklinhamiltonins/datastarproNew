<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_files', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('file_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
           $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['lead_id','file_id']);
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
    }
}
