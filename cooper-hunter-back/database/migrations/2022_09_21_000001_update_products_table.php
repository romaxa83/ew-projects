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
                $table->json('olmo_additions')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('olmo_additions');
            }
        );
    }
};

