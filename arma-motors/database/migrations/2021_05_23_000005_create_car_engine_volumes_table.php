<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarEngineVolumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_engine_volumes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->integer('volume');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_engine_volumes');
    }
}
