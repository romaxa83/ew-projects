<?php

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryFeature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(InventoryFeature::TABLE, function (Blueprint $table) {

            $table->unsignedBigInteger('inventory_id');
            $table->foreign('inventory_id')
                ->references('id')
                ->on(Inventory::TABLE)
                ->onDelete('cascade')
            ;

            $table->unsignedBigInteger('feature_id');
            $table->foreign('feature_id')
                ->references('id')
                ->on(Feature::TABLE)
                ->onDelete('cascade')
            ;

            $table->unsignedBigInteger('value_id');
            $table->foreign('value_id')
                ->references('id')
                ->on(Value::TABLE)
                ->onDelete('cascade')
            ;

            $table->primary(['inventory_id', 'feature_id', 'value_id'], 'prk_inventory-feature-value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(InventoryFeature::TABLE);
    }
};
