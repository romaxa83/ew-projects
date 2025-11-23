<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collection_product_relations',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('collection_id');
                $table->foreign('collection_id')
                    ->references('id')
                    ->on(Collection::TABLE);
                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->onDelete('cascade');

                $table->primary(['collection_id', 'product_id']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product_relations');
    }
};
