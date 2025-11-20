<?php

use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(Admin::TABLE,
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
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
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Admin::TABLE);
    }
};
