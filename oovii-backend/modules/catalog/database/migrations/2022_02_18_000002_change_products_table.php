<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Product;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->foreignId('brand_id')
                    ->after('published')
                    ->nullable()->constrained()
                    ->nullOnDelete();

                $table->foreignId('category_id')
                    ->after('published')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');

                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        );
    }
};

