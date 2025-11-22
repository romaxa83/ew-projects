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
            $table->string('stock_number')->unique()->change();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_stock')->default(false);
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->string('stock_number')->change();
            $table->dropUnique('inventories_stock_number_unique');

            $table->dropColumn('is_new');
            $table->dropColumn('is_popular');
            $table->dropColumn('is_stock');
            $table->dropColumn('old_price');
            $table->dropColumn('discount');
        });
    }
};

