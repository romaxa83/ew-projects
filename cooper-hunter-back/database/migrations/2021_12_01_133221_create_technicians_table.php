<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'technicians',
            static function (Blueprint $table) {
                $table->id();

                $table->boolean('is_certified')->default(false);

                $table->string('first_name');
                $table->string('last_name');
                $table->string('middle_name')->nullable();

                $table->string('email')->unique();
                $table->string('phone', 24)->nullable()->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('phone_verified_at')->nullable();

                $table->string('email_verification_code', 16)->nullable();

                $table->string('password');
                $table->rememberToken();

                $table->string('lang')->nullable();

                $table->foreign('lang')
                    ->on('languages')
                    ->references('slug')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};
