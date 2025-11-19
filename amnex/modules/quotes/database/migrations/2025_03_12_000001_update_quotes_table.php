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

            $table->decimal('calc_price', 10, 2)->default(0);
            $table->integer('piece_count')->nullable();
            $table->softDeletes();

            $table->dropColumn('piece_pallets');
        });
    }

    public function down(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->dropColumn('pickup_terminal_id');
            $table->dropColumn('delivery_terminal_id');
            $table->dropColumn('calc_price');
            $table->dropColumn('piece_count');
            $table->dropColumn('deleted_at');
            $table->integer('piece_pallets')->nullable();
        });
    }
};
