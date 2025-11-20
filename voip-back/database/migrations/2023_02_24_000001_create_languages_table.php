<?php

use App\Models\Localization\Language;
use Database\Seeders\LanguageDefaultSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Language::TABLE,
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug', 3)->unique();
                $table->boolean('default')
                    ->default(0);
                $table->timestamps();
            }
        );

        resolve(LanguageDefaultSeeder::class)->run();
    }

    public function down(): void
    {
        Schema::table(Language::TABLE, function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
        Schema::dropIfExists(Language::TABLE);
    }
};
