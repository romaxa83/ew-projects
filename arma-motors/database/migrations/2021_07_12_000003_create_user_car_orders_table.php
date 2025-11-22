<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCarOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_car_orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('car_id');
            $table->foreign('car_id')
                ->references('id')->on('user_cars')
                ->onDelete('cascade');

            $table->tinyInteger('payment_status')->default(\App\Models\User\OrderCar\OrderCar::NONE);
            $table->integer('sum')->nullable();
            $table->integer('sum_discount')->nullable();
            $table->string('order_number')->nullable();
            $table->integer('files')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_car_orders');
    }
}
