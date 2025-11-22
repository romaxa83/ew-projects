<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('order_tool_ticket', 'ticket_order_category');
    }

    public function down(): void
    {
        Schema::rename('ticket_order_category', 'order_tool_ticket');
    }
};
