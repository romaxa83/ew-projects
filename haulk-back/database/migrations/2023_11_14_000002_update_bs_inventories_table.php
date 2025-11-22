<?php

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Inventory::TABLE_NAME,
            static function (Blueprint $table) {
                $table->decimal('length', 10, 2)->nullable();
                $table->decimal('width', 10, 2)->nullable();
                $table->decimal('height', 10, 2)->nullable();
                $table->decimal('weight', 10, 2)->nullable();
                $table->decimal('min_limit_price', 10, 2)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropColumn('length');
                $table->dropColumn('width');
                $table->dropColumn('height');
                $table->dropColumn('weight');
                $table->dropColumn('min_limit_price');
            }
        );
    }
};
