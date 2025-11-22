<?php

use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_label',
            static function (Blueprint $table) {

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->on(Product::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('label_id');
                $table->foreign('label_id')
                    ->on(Label::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->primary(['product_id', 'label_id'], 'pk-cpll_primary');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_label');
    }
};
