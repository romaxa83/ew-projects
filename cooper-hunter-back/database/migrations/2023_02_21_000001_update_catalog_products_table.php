<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->string('unit_sub_type', 10)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('unit_sub_type');
            }
        );
    }
};
