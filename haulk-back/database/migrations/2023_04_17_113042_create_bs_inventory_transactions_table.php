<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBsInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bs_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')
                ->nullable()
                ->index()
                ->references('id')->on('bs_inventories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('order_id')
                ->nullable()
                ->index()
                ->references('id')->on('bs_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->date('transaction_date')->index();
            $table->float('quantity');
            $table->decimal('price');
            $table->string('invoice_number');
            $table->string('comment')->nullable();
            $table->string('operation_type')->index();
            $table->boolean('is_reserve')->default(false);
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
        Schema::dropIfExists('bs_inventory_transactions');
    }
}
