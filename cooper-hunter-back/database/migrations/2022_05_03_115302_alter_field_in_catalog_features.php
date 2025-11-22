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
                $table
                    ->boolean('display_in_filter')
                    ->default(true)
                    ->after('display_in_mobile');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_features',
            static function (Blueprint $table) {
                $table->dropColumn('display_in_filter');
            }
        );
    }
};
