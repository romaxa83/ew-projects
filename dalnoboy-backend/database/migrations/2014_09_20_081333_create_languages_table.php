<?php

use Database\Seeders\LanguageSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'languages',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug', 3)->unique();
                $table->boolean('default')
                    ->default(0);
                $table->timestamps();
            }
        );

        resolve(LanguageSeeder::class)->run();
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
        Schema::dropIfExists('languages');
    }
};
