<?php

use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Parts\Item::TABLE, function (Blueprint $table) {
            $table->foreignId('shipping_id')
                ->nullable()
                ->references('id')
                ->on(Parts\Shipping::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->boolean('free_shipping')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Parts\Item::TABLE, function (Blueprint $table) {
            $table->dropForeign('parts_order_items_shipping_id_foreign');
            $table->dropColumn('shipping_id');
            $table->dropColumn('free_shipping');
        });
    }
};
