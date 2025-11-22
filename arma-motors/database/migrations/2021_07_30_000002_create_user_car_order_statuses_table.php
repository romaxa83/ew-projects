<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCarOrderStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('user_car_order_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('status', 20);

            $table->unsignedBigInteger('order_car_id');
            $table->foreign('order_car_id')
                ->references('id')
                ->on('user_car_orders')
                ->onDelete('cascade');

            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')
                ->references('id')
                ->on('car_order_statuses')
                ->onDelete('cascade');

            $table->timestamp('date_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_car_order_statuses');
    }
}
