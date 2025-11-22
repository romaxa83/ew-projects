<?php

use App\Models\Catalog\Products\Product;
use App\Models\Dealers\Dealer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dealer_prices',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('dealer_id');
                $table->foreign('dealer_id')
                    ->references('id')
                    ->on(Dealer::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('price')->default(0);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_prices');
    }
};
