<?php

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Dealer\PackingSlipItem::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('packing_slip_id');
                $table->foreign('packing_slip_id')
                    ->references('id')
                    ->on(Dealer\PackingSlip::TABLE)
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
                $table->unsignedInteger('total')->default(0);

                $table->mediumText('description')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Dealer\PackingSlipItem::TABLE);
    }
};
