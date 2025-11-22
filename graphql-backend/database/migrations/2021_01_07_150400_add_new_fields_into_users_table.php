<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->string('phone', 24)
                    ->nullable()
                    ->unique()
                    ->after('email');

                $table->timestamp('phone_verified_at')
                    ->nullable()
                    ->after('email_verified_at');

                $table->string('email_verification_code', 16)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->dropColumn(['phone', 'phone_verified_at', 'email_verification_code']);
            }
        );
    }
};
