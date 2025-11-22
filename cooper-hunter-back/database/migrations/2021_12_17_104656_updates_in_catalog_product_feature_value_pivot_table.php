<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_product_feature_value_pivot',
            static function (Blueprint $table) {
                $table->dropForeign(['feature_id']);
                $table->dropColumn('feature_id');

                $table->unique(['product_id', 'value_id']);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_product_feature_value_pivot',
            static function (Blueprint $table) {
                $table->dropForeign(['product_id']);
                $table->dropForeign(['value_id']);

                $table->dropUnique(['product_id', 'value_id']);

                $table->foreignId('feature_id')
                    ->references('id')
                    ->on('catalog_features')
                    ->onDelete('cascade');

                $table->foreign('product_id')
                    ->references('id')
                    ->on('catalog_products')
                    ->onDelete('cascade');

                $table->foreign('value_id')
                    ->references('id')
                    ->on('catalog_feature_values')
                    ->onDelete('cascade');
            }
        );
    }
};
