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
                $table->integer('likes')->default(0);
                $table->integer('dislikes')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('likes');
                $table->dropColumn('dislikes');
            }
        );
    }
};

