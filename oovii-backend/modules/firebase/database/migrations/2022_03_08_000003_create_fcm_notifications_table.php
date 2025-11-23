<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Users\Models\User;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(FcmNotification::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('user_id')
                    ->nullable();
                $table->foreign('user_id')
                    ->references('id')
                    ->on(User::TABLE)
                    ->onDelete('cascade');

                $table->string('entity_type', 350)
                    ->nullable();
                $table->integer('entity_id')
                    ->nullable();
                $table->string('status', 20)
                    ->default(FcmNotification::STATUS_CREATED);
                $table->string('type', 40)->nullable();
                $table->json('send_data');
                $table->json('response_data')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(FcmNotification::TABLE);
    }
};
