<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\TerminalDistance;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('terminal_distance_id')
                ->nullable();
            $table->foreign('terminal_distance_id')
                ->references('id')
                ->on(TerminalDistance::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->dropColumn('terminal_distance_id');
        });
    }
};
