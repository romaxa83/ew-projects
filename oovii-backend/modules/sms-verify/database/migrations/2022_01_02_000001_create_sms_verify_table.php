<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\SmsVerify\Models\SmsVerify;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(SmsVerify::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('phone');
                $table->string('code');
                $table->string('sms_token')->nullable()->index()->unique();
                $table->timestamp('sms_token_expires')->nullable();
                $table->string('action_token')->nullable()->index()->unique();
                $table->timestamp('action_token_expires')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(SmsVerify::TABLE);
    }
};
