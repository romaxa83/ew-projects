<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')
                ->references('id')
                ->on('cities')
                ->onDelete('cascade');

            $table->unsignedBigInteger('dealership_id')->nullable();
            $table->foreign('dealership_id')
                ->references('id')
                ->on('dealerships')
                ->onDelete('cascade');

            $table->unsignedBigInteger('car_id')->nullable();
            $table->foreign('car_id')
                ->references('id')
                ->on('user_cars')
                ->onDelete('cascade');
            $table->boolean('is_users_vehicle')->default(false);
            $table->json('vehicle')->nullable();

            $table->unsignedBigInteger('service_group_id')->nullable();
            $table->foreign('service_group_id')
                ->references('id')
                ->on('service_groups')
                ->onDelete('cascade');

            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->tinyInteger('status')->default(\WezomCms\ServicesOrders\Types\OrderStatus::CREATED);
            $table->dateTime('on_date')->nullable();
            $table->boolean('recall')->default(false);

            $table->string('comment')->nullable();
            $table->json('additional')->nullable();
            $table->tinyInteger('rating_services')->default(0)->comment('Оценка обслуживания авто');
            $table->tinyInteger('rating_order')->default(0)->comment('Оценка записи на обслуживание обслуживания авто');
            $table->string('rating_comment')->nullable();

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
        Schema::dropIfExists('services_orders');
    }
}
