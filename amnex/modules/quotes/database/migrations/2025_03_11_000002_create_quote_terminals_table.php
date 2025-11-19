<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\QuoteTerminal;
use Wezom\Quotes\Models\Terminal;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(QuoteTerminal::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('quote_id');
            $table->foreign('quote_id')
                ->references('id')
                ->on(Quote::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('terminal_id');
            $table->foreign('terminal_id')
                ->references('id')
                ->on(Terminal::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('type', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(QuoteTerminal::TABLE);
    }
};
