<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_categories',
            static function (Blueprint $table) {
                $table->string('slug')->after('id');
            }
        );

        Schema::table(
            'catalog_categories',
            static function (Blueprint $table) {
                DB::statement(
                    'update catalog_categories set slug = concat(\'slug-value-\', catalog_categories.id) where true;'
                );

                $table->unique('slug');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_categories',
            static function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        );
    }
};
