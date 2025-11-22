<?php

use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Dealer\PackingSlipItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(PackingSlipItem::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('order_item_id')->nullable();
                $table->foreign('order_item_id')
                    ->references('id')
                    ->on(Item::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->dropColumn('price');
                $table->dropColumn('discount');
                $table->dropColumn('total');
            }
        );
    }

    public function down(): void
    {
        Schema::table(PackingSlipItem::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign(['order_item_id']);
                $table->dropColumn(['order_item_id']);

                $table->unsignedInteger('price')->default(0);
                $table->unsignedInteger('discount')->default(0);
                $table->unsignedInteger('total')->default(0);
            }
        );
    }
};
