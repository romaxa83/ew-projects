<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->dropUnique(['title']);
            }
        );

        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->unique(['feature_id', 'title'], 'feature_id_title_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->dropForeign(['feature_id']);
            }
        );

        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->dropUnique('feature_id_title_unique');
                $table->unique(['title']);
            }
        );

        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->foreign('feature_id')
                    ->references('id')
                    ->on('catalog_features')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }
};
