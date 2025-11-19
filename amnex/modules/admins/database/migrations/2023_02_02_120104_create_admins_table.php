<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(
            'admins',
            function (Blueprint $table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->unique();
                $table->string('password')->nullable();

                $table->string('phone')->nullable();
                $table->boolean('verified')->default(false);
                $table->string('verification_code', 16)->nullable();
                $table->boolean('active')->default(true);
                $table->string('remember_token', 1024)->nullable();

                $table->string('email_verification_code', 16)->nullable();
                $table->string('new_email_for_verification')->nullable();
                $table->string('new_email_verification_code')->nullable();
                $table->timestamp('new_email_verification_code_at')->nullable();

                $table->string('status')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
