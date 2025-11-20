<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionTranslatesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('region_translations', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('region_id');
			$table->string('locale')->index();
			$table->string('name')->nullable();

			$table->unique(['region_id', 'locale']);
			$table->foreign('region_id')
				->references('id')
				->on('regions')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('regions_translations');
	}
}

