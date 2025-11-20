<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class CreateUserCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('vin_code')->nullable();
            $table->string('number')->nullable();
            $table->string('year')->nullable();
            $table->string('millage')->nullable()->comment('Пробег авто (км)');
            $table->boolean('is_family_car')->default(false);
            $table->boolean('is_verify')->default(false);
            $table->string('engine_description')->nullable();

            $table->unsignedBigInteger('dealership_id')->nullable();
            $table->foreign('dealership_id')
                ->references('id')
                ->on('dealerships');

            $table->unsignedBigInteger('brand_id')->nullable();
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands');

            $table->unsignedBigInteger('model_id')->nullable();
            $table->foreign('model_id')
                ->references('id')
                ->on('car_models');

            $table->unsignedBigInteger('transmission_id')->nullable();
            $table->foreign('transmission_id')
                ->references('id')
                ->on('car_transmissions');

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
        Schema::dropIfExists('user_cars');
    }
}

