<?php

use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Transaction::TABLE, function (Blueprint $table) {
            $table->foreignId('order_parts_id')
                ->after('order_id')
                ->nullable()
                ->index()
                ->references('id')
                ->on(Parts\Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(Transaction::TABLE, function (Blueprint $table) {
            $table->dropForeign('inventory_transactions_order_parts_id_foreign');
            $table->dropColumn('order_parts_id');
        });
    }
};
