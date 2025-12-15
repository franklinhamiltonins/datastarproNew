<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('c_secondary_email', 255)->nullable();
            $table->string('c_secondary_phone', 255)->nullable();
            $table->enum('auto_responder', [0, 1])->default(0);
            $table->date('last_sms')->nullable();
            $table->date('last_email')->nullable();
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
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('c_secondary_email');
            $table->dropColumn('c_secondary_phone');
            $table->dropColumn('auto_responder');
            $table->dropColumn('last_sms');
            $table->dropColumn('last_email');
        });
    }
}
