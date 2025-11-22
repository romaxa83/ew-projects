<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'member_phone_verifications',
            static function (Blueprint $table) {
                $table->id();
                $table->string('phone');
                $table->string('code');

                $table->string('sms_token')->unique();
                $table->timestamp('sms_token_expires_at');

                $table->string('access_token')->unique()->nullable();
                $table->timestamp('access_token_expires_at')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('member_phone_verifications');
    }
};
