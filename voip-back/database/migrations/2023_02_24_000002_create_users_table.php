<?php

use App\Models\Localization\Language;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(User::TABLE,
            function (Blueprint $table) {
                $table->id();
                $table->string('first_name', 75);
                $table->string('last_name', 75);
                $table->string('full_name_normalized', 150)
                    ->storedAs("regexp_replace(last_name || ' ' || first_name, '[^A-Za-zА-Яа-я0-9 ]/i', '')")
                    ->index();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->boolean('active')->default(true);

                $table->string('lang', 4)
                    ->nullable()
                    ->default(app('localization')->getDefaultSlug())
                    ->default('en');
                $table->foreign('lang')
                    ->on(Language::TABLE)
                    ->references('slug')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->softDeletes();
                $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(User::TABLE);
    }
};
