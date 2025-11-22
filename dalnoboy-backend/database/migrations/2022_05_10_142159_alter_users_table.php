<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'users',
            static function (Blueprint $table) {
                $table->string('authorization_expiration_period')
                    ->default(\App\Enums\Users\AuthorizationExpirationPeriodEnum::UNLIMITED);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'users',
            static function (Blueprint $table) {
                $table->dropColumn('authorization_expiration_period');
            }
        );
    }
};
