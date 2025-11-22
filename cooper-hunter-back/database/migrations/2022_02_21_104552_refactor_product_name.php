<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_product_translations',
            static function (Blueprint $table) {
                $table->dropColumn(['slug', 'title']);
            }
        );

        Schema::table(
            'catalog_products',
            static function (Blueprint $table) {
                $table->dropUnique(['vendor_code']);
                $table->dropUnique(['model']);
                $table->dropColumn(['vendor_code', 'model']);

                $table->after('active', static function (Blueprint $t) {
                    $t->string('slug')->unique();
                    $t->string('title');
                    $t->string('title_metaphone');
                });
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_product_translations',
            static function (Blueprint $table) {
                $table->string('slug')->index();
                $table->string('title');
            }
        );

        Schema::table(
            'catalog_products',
            static function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn(['slug', 'title', 'title_metaphone']);

                $table->string('vendor_code')->nullable()->unique();
                $table->string('model')->nullable()->unique();
            }
        );
    }
};
