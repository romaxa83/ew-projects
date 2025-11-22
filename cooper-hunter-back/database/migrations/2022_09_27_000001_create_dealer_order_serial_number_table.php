<?php

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\SerialNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(SerialNumber::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('order_id');
                $table->foreign('order_id')
                    ->on(Order::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->on(Product::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('serial_number')->nullable();
                $table->unique(['product_id', 'serial_number']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(SerialNumber::TABLE);
    }
};
