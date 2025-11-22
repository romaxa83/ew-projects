<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                ->references('id')->on('services');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users');

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')
                ->references('id')->on('admins');

            $table->string('communication');
            $table->tinyInteger('status')->default(\App\Types\Order\Status::DRAFT);
            $table->dateTime('closed_at')->nullable();
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
        Schema::dropIfExists('orders');
    }
}


