<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCarConfidantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_car_confidants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('car_id');
            $table->foreign('car_id')
                ->references('id')->on('user_cars')
                ->onDelete('cascade');

            $table->string('name')->nullable();
            $table->string('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_car_confidants');
    }
}

