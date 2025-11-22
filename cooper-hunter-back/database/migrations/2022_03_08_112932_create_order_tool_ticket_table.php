<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'order_tool_ticket',
            static function (Blueprint $table) {
                $table->foreignId('order_category_id')
                    ->constrained('order_categories')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('ticket_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedInteger('quantity')
                    ->default(1);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tool_ticket');
    }
};
