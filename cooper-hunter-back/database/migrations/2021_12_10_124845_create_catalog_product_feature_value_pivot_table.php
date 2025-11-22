<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_product_feature_value_pivot', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('catalog_products')
                ->onDelete('cascade');

            $table->unsignedBigInteger('feature_id');
            $table->foreign('feature_id')
                ->references('id')
                ->on('catalog_features')
                ->onDelete('cascade');

            $table->unsignedBigInteger('value_id');
            $table->foreign('value_id')
                ->references('id')
                ->on('catalog_feature_values')
                ->onDelete('cascade');

            $table->primary(['product_id', 'feature_id', 'value_id'], 'pk-cpfvp_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_feature_value_pivot');
    }
};
