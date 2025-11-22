<?php

use App\Models\Inventories\Brand;
use App\Models\Inventories\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')
                ->after('supplier_id')
                ->nullable();
            $table->foreign('brand_id')
                ->references('id')
                ->on(Brand::TABLE);
        });
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->dropColumn('brand_id');
        });
    }
};
