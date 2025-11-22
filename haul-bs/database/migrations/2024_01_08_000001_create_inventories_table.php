<?php

use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Inventory::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')
                ->references('id')
                ->on(Category::TABLE);
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')
                ->references('id')
                ->on(Unit::TABLE);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')
                ->references('id')
                ->on(Supplier::TABLE);

            $table->boolean('active')->default(true);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('stock_number');
            $table->decimal('price_retail', 10, 2)->nullable();
            $table->decimal('min_limit_price', 10, 2)->nullable();
            $table->float('quantity');
            $table->float('min_limit')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('for_shop')->default(true);
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();

            $table->integer('origin_id')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Inventory::TABLE);
    }
};
