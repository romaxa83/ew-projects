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
                $table->after(
                    'id',
                    static function (Blueprint $table) {
                        $table->foreignId('feature_id')
                            ->references('id')
                            ->on('catalog_features')
                            ->cascadeOnUpdate()
                            ->cascadeOnDelete();
                    }
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('feature_id');
            }
        );
    }
};
