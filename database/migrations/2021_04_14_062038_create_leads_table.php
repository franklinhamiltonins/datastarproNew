<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable();
            $table->string('name')->unique();
            $table->date('creation_date')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();  
            $table->string('county')->nullable();
            $table->bigInteger('unit_count')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('renewal_month')->nullable();
            $table->decimal('premium', 18, 2)->nullable();
            $table->decimal('insured_amount', 18, 2)->nullable();
            $table->string('manag_company')->nullable();
            $table->string('prop_manager')->nullable();
            $table->string('current_agency')->nullable();
            $table->string('current_agent')->nullable();
            $table->string('ins_prop_carrier')->nullable();
            $table->string('ins_flood')->nullable();
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
        Schema::dropIfExists('leads');
    }
}
