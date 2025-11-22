<?php

use App\Foundations\Modules\Localization\Models\Language;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(User::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('status', 15);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('second_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verified_code', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('phone_extension', 20)->nullable();
            $table->json('phones')->nullable();
            $table->string('password')->nullable();
            $table->string('password_verified_code', 10)->nullable();

            $table->string('lang', 3);
            $table->foreign('lang')
                ->references('slug')
                ->on(Language::TABLE)
                ->onUpdate('cascade');

            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(User::TABLE);
    }
};
