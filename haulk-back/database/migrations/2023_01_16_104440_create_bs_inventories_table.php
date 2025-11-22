<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBsInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bs_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stock_number');
            $table->foreignId('category_id')
                ->nullable()
                ->references('id')->on('bs_inventory_categories')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->decimal('price_wholesale', 10, 2);
            $table->decimal('price_retail', 10, 2)->nullable();
            $table->integer('quantity');
            $table->foreignId('supplier_id')
                ->nullable()
                ->references('id')->on('bs_suppliers')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bs_inventories');
    }
}
