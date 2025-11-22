<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_product_relations_pivot', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('catalog_products')
                ->onDelete('cascade');

            $table->unsignedBigInteger('relation_id');
            $table->foreign('relation_id')
                ->references('id')
                ->on('catalog_products')
                ->onDelete('cascade');

            $table->primary(['product_id', 'relation_id'], 'pk-cprv_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_relations_pivot');
    }
};

