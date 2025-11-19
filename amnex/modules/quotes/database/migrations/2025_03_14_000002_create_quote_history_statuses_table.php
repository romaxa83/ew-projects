<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\QuoteHistoryStatus;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(QuoteHistoryStatus::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('quote_id');
            $table->foreign('quote_id')
                ->references('id')
                ->on(Quote::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('prev_status', 50)->nullable();
            $table->string('new_status', 50);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(QuoteHistoryStatus::TABLE);
    }
};
