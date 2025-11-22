<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'catalog_products',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(Product::DEFAULT_SORT);
                $table->boolean('active')->default(Product::DEFAULT_ACTIVE);

                $table->unsignedBigInteger('category_id');
                $table->foreign('category_id')
                    ->on('catalog_categories')
                    ->references('id')
                    ->onDelete('cascade');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
