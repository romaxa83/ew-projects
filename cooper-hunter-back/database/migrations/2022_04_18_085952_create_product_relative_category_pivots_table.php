<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'product_relative_category',
            static function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->constrained(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreignId('category_id')
                    ->constrained(\App\Models\Catalog\Categories\Category::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_relative_category');
    }
};
