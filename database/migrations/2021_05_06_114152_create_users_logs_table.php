<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_logs', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('log_id');

         //FOREIGN KEY CONSTRAINTS
           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
           $table->foreign('log_id')->references('id')->on('logs')->onDelete('cascade');

         //SETTING THE PRIMARY KEYS
           $table->primary(['user_id','log_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_logs');
    }
}
