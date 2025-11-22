<?php

use Database\Seeders\LanguageSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{

    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 3)->unique();
            $table->string('locale', 10)->unique();
            $table->boolean('default')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropUnique(['locale']);
        });
        Schema::dropIfExists('languages');
    }
}

