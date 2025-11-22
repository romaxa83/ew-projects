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
            $table->boolean('new_item')->default(false)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Inventory::TABLE, function (Blueprint $table) {
            $table->dropColumn('new_item');
        });
    }
};
