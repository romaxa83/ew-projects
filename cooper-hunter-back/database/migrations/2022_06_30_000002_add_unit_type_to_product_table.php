<?php

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('unit_type_id')->nullable();
                $table->foreign('unit_type_id')
                    ->references('id')
                    ->on(UnitType::TABLE)
                    ->index('idx_unit_type_product_id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE, function (Blueprint $table) {
            $table->dropForeign('idx_unit_type_product_id');
            $table->dropColumn(['unit_type_id']);
        });
    }
};
