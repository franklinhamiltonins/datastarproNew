<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentInsurancesColumnsOnLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {

            //add Current Insurances columns
            $table->string('general_liability')->after('ins_flood')->nullable();
            $table->string('GL_ren_month')->after('general_liability')->nullable();
            $table->string('crime_insurance')->after('GL_ren_month')->nullable();
            $table->string('CI_ren_month')->after('crime_insurance')->nullable();
            $table->string('directors_officers')->after('CI_ren_month')->nullable();
            $table->string('DO_ren_month')->after('directors_officers')->nullable();
            $table->string('umbrella')->after('DO_ren_month')->nullable();
            $table->string('U_ren_month')->after('umbrella')->nullable();
            $table->string('workers_compensation')->after('U_ren_month')->nullable();
            $table->string('WC_ren_month')->after('workers_compensation')->nullable();
            $table->string('flood')->after('WC_ren_month')->nullable();
            $table->string('F_ren_month')->after('flood')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('general_liability');
            $table->dropColumn('GL_ren_month');
            $table->dropColumn('crime_insurance');
            $table->dropColumn('CI_ren_month');
            $table->dropColumn('directors_officers');
            $table->dropColumn('DO_ren_month');
            $table->dropColumn('umbrella');
            $table->dropColumn('U_ren_month');
            $table->dropColumn('workers_compensation');
            $table->dropColumn('WC_ren_month');
            $table->dropColumn('flood');
            $table->dropColumn('F_ren_month');
        });
    }
}
