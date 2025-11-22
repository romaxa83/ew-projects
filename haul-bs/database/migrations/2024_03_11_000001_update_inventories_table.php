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
            $table->decimal('delivery_cost', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->dropColumn('delivery_cost');
        });
    }
};
