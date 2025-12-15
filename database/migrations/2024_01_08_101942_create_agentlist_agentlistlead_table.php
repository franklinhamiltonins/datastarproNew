<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentlistAgentlistleadTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('agentlist_agentlistlead', function (Blueprint $table) {
			$table->id();
			$table->foreignId('agentlist_id')->constrained();
			$table->foreignId('agentlistlead_id')->constrained();
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
		Schema::dropIfExists('agentlist_agentlistlead');
	}
}
