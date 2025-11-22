<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalcModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calc_models', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands')
                ->onDelete('cascade');

            $table->unsignedBigInteger('model_id');
            $table->foreign('model_id')
                ->references('id')
                ->on('car_models')
                ->onDelete('cascade');

            $table->unsignedBigInteger('mileage_id')->nullable();
            $table->foreign('mileage_id')
                ->references('id')
                ->on('mileages')
                ->onDelete('cascade');

            $table->unsignedBigInteger('engine_volume_id')->nullable();
            $table->foreign('engine_volume_id')
                ->references('id')
                ->on('car_engine_volumes')
                ->onDelete('cascade');

            $table->unsignedBigInteger('transmission_id')->nullable();
            $table->foreign('transmission_id')
                ->references('id')
                ->on('transmissions')
                ->onDelete('cascade');

            $table->unsignedBigInteger('drive_unit_id')->nullable();
            $table->foreign('drive_unit_id')
                ->references('id')
                ->on('drive_units')
                ->onDelete('cascade');

            $table->unsignedBigInteger('fuel_id')->nullable();
            $table->foreign('fuel_id')
                ->references('id')
                ->on('fuels')
                ->onDelete('cascade');

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
        Schema::dropIfExists('calc_models');
    }
}
