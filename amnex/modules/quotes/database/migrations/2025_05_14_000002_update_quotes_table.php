<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\Terminal;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->string('delivery_address', 500)
                ->nullable();

            $table->dropColumn([
                'delivery_terminal_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->dropColumn([
                'delivery_address',
            ]);

            $table->unsignedBigInteger('delivery_terminal_id');
            $table->foreign('delivery_terminal_id')
                ->references('id')
                ->on(Terminal::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
};

