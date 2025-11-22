<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('catalog_feature_values_pivot');
    }

    public function down(): void
    {
        Schema::create(
            'catalog_feature_values_pivot',
            static function (Blueprint $table) {
                $table->foreignId('feature_id')
                    ->references('id')
                    ->on('catalog_features')
                    ->onDelete('cascade');

                $table->foreignId('value_id')
                    ->references('id')
                    ->on('catalog_feature_values')
                    ->onDelete('cascade');
            }
        );
    }
};
