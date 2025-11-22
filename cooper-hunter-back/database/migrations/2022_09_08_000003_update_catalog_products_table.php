<?php

use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->foreign('brand_id')
                    ->references('id')
                    ->on(Brand::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            }
        );
    }
};
