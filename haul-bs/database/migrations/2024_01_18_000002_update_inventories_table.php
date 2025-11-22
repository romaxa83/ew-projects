<?php

use App\Models\Inventories\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->dropUnique('inventories_stock_number_unique');
            $table->string('stock_number')->change();
        });
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->string('stock_number')->unique()->change();
        });
    }
};
