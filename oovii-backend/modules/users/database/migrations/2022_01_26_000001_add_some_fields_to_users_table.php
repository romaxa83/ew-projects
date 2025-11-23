<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(User::TABLE,
            static function (Blueprint $table) {
                $table->tinyInteger('status')
                    ->after('id')
                    ->default(UserStatus::DRAFT);

                $table->boolean('phone_verified')
                    ->after('phone')
                    ->default(false);
                $table->string('fcm_token')->nullable();
                $table->string('device_id')->nullable();
                $table->string('lang')
                    ->default(config('cms.core.translations.app.default'));
            }
        );
    }

    public function down(): void
    {
        Schema::table(User::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn("status");
                $table->dropColumn("phone_verified");
                $table->dropColumn("fcm_token");
                $table->dropColumn("device_id");
                $table->dropColumn("lang");
            }
        );
    }
};

