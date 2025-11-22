<?php

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->string('owner_type', 20)
                    ->default(ProductOwnerType::COOPER());
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('owner_type');
            }
        );
    }
};
