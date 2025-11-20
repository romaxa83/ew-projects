<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcmNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fcm_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('service_order_id')->nullable();
            $table->foreign('service_order_id')
                ->references('id')
                ->on('services_orders');
            $table->tinyInteger('type')->default(\WezomCms\Firebase\Types\FcmNotificationType::NONE);
            $table->tinyInteger('status')->default(\WezomCms\Firebase\Types\FcmNotificationStatus::CREATED);
            $table->json('data');
            $table->json('error_data')->nullable();
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
        Schema::dropIfExists('fcm_notifications');
    }
}
