<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcmNotificationTable extends Migration
{
    public function up(): void
    {
        Schema::create('fcm_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 350)->nullable();
            $table->integer('entity_id')->nullable();
            $table->string('status', 20)->default(\App\Models\Notification\FcmNotification::STATUS_CREATED);
            $table->string('action', 40);
            $table->json('send_data');
            $table->json('response_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_notifications');
    }
}
