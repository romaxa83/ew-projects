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
                $table->boolean('for_sale')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropColumn('for_sale');
            }
        );
    }
};
