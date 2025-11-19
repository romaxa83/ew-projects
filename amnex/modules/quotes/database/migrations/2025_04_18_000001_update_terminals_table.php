<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Terminal;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Terminal::TABLE, function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->json('coords')->nullable();
            $table->string('link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Terminal::TABLE, function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'coords',
                'link',
            ]);
        });
    }
};
