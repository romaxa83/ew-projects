<?php

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Dealer\Item::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('order_id');
                $table->foreign('order_id')
                    ->references('id')
                    ->on(Dealer\Order::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('price')->default(0);
                $table->unsignedInteger('qty')->default(0);
                $table->unsignedInteger('discount')->default(0);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Dealer\Item::TABLE);
    }
};
