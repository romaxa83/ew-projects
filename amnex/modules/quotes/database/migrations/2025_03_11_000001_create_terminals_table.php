<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Terminal;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(Terminal::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Terminal::TABLE);
    }
};
