<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Terminal;
use Wezom\Quotes\Models\TerminalDistance;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(TerminalDistance::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pickup_terminal_id');
            $table->foreign('pickup_terminal_id')
                ->references('id')
                ->on(Terminal::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('delivery_terminal_id');
            $table->foreign('delivery_terminal_id')
                ->references('id')
                ->on(Terminal::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('distance_as_mile', 10, 2)
                ->default(0);
            $table->decimal('distance_as_meters', 10, 2)
                ->default(0);
            $table->string('distance_text', 10)->nullable();
            $table->json('start_location')->nullable();
            $table->json('end_location')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TerminalDistance::TABLE);
    }
};
