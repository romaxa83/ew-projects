<?php

use App\Models\Inventories\Inventory;
use App\Models\Orders;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Parts\Item::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->nullable()
                ->references('id')
                ->on(Parts\Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('inventory_id')
                ->nullable()
                ->references('id')
                ->on(Inventory::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('qty', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Parts\Item::TABLE);
    }
};
