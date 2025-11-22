<?php

use App\Models\Orders\Parts\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->decimal('delivery_cost', 10, 2)
                ->default(0);
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('delivery_cost');
        });
    }
};
