<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->morphs('tokenable');
            $table->unsignedBigInteger('access_token_id');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('session_id')
                ->references('id')
                ->on('personal_sessions')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('access_token_id')
                ->references('id')
                ->on('personal_access_tokens')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_refresh_tokens');
    }
};
