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
                $table->boolean('show_rebate')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE, function (Blueprint $table) {
            $table->dropColumn(['show_rebate']);
        });
    }
};

