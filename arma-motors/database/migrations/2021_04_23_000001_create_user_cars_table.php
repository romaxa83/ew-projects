<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('uuid', 100)->index()->nullable();

            $table->unsignedBigInteger('model_id');
            $table->foreign('model_id')
                ->references('id')
                ->on('car_models')
                ->onDelete('cascade');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands')
                ->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('number', 20)->nullable();
            $table->string('vin', 50)->nullable();
            $table->year('year')->nullable();
            $table->tinyInteger('inner_status')->nullable(\App\Models\User\Car::DRAFT);
            $table->boolean('is_verify')->default(false);
            $table->boolean('is_moderate')->default(false);
            $table->boolean('is_personal')->default(true);
            $table->boolean('is_buy')->default(false);
            $table->boolean('is_add_to_app')->default(false);
            $table->boolean('selected')->default(false);

            $table->softDeletes();
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
