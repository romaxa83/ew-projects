<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\Terminal;
use Wezom\Quotes\Models\TerminalDistance;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(TerminalDistance::TABLE, function (Blueprint $table) {
            $table->string('delivery_address', 500);
            $table->json('delivery_data')->nullable();

            $table->dropColumn([
                'delivery_terminal_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table(TerminalDistance::TABLE, function (Blueprint $table) {
            $table->dropColumn([
                'delivery_address',
                'delivery_data',
            ]);

            $table->unsignedBigInteger('terminal_distance_id')
                ->nullable();
            $table->foreign(TerminalDistance::TABLE)
                ->references('id')
                ->on('TerminalDistance')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
};

