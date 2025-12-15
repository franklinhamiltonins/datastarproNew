<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterScrapApiPlatform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('scrap_api_platforms', function (Blueprint $table) {
            $table->date('auth_expiry_date')->nullable();
            $table->enum('auth_token_required', [0, 1])->default(0);
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
        Schema::table('scrap_api_platforms', function (Blueprint $table) {
            $table->dropColumn('auth_expiry_date');
            $table->dropColumn('auth_token_required');
        });
    }
}
