<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAdditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_additions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')
                ->on('orders')->onDelete('cascade');

            $table->unsignedBigInteger('franchise_id')->nullable();
            $table->foreign('franchise_id')
                ->references('id')->on('insurance_franchise');

            $table->unsignedBigInteger('brand_id')->nullable();
            $table->foreign('brand_id')
                ->references('id')->on('car_brands');

            $table->unsignedBigInteger('model_id')->nullable();
            $table->foreign('model_id')
                ->references('id')->on('car_models');

            $table->unsignedBigInteger('region_id')->nullable();
            $table->foreign('region_id')
                ->references('id')->on('regions');

            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')
                ->references('id')->on('cities');

            $table->unsignedBigInteger('transport_type_id')->nullable();
            $table->foreign('transport_type_id')
                ->references('id')->on('transport_types');

            $table->unsignedBigInteger('privileges_id')->nullable();
            $table->foreign('privileges_id')
                ->references('id')->on('privileges');

            $table->unsignedBigInteger('driver_age_id')->nullable();
            $table->foreign('driver_age_id')
                ->references('id')->on('driver_ages');

            $table->unsignedBigInteger('duration_id')->nullable();
            $table->foreign('duration_id')
                ->references('id')->on('service_durations');

            $table->string('insurance_company')->nullable();
            $table->bigInteger('car_cost')->nullable();
            $table->tinyInteger('count_pay')->nullable();
            $table->boolean('use_as_taxi')->default(false);
            $table->tinyInteger('type_user')->nullable();
            $table->integer('first_installment_percent')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_additions');
    }
}

