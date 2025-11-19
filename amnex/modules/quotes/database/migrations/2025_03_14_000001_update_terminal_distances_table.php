<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\TerminalDistance;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(TerminalDistance::TABLE, function (Blueprint $table) {
            $table->string('distance_text', 50)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table(TerminalDistance::TABLE, function (Blueprint $table) {
            $table->string('distance_text', 10)
                ->nullable()
                ->change();
        });
    }
};
