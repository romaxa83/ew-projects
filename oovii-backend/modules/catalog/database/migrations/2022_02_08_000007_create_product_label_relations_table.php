<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Catalog\Models\Product;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_label_relations',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->onDelete('cascade');

                $table->unsignedBigInteger('label_id');
                $table->foreign('label_id')
                    ->references('id')
                    ->on(Label::TABLE)
                    ->onDelete('cascade');

                $table->primary(['product_id', 'label_id']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_label_relations');
    }
};
