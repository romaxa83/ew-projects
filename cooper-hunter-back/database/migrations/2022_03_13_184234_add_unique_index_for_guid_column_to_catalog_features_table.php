<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_features',
            static function (Blueprint $table) {
                $table->unique('guid', 'catalog_features_guid_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_features',
            static function (Blueprint $table) {
                $table->dropIndex('catalog_features_guid_unique');
            }
        );
    }
};
