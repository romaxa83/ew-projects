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
            $table->dropColumn('is_stock');
            $table->boolean('is_sale')->default(false);

            $table->string('package_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->boolean('is_stock')->default(false);
            $table->dropColumn('is_sale');
            $table->dropColumn('package_type');
        });
    }
};
