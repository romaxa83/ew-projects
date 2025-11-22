<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBsOrderTypeOfWorkInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bs_order_type_of_work_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_of_work_id')
                ->nullable()
                ->references('id')->on('bs_order_types_of_work')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('inventory_id')
                ->nullable()
                ->references('id')->on('bs_inventories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('quantity');
            $table->float('price');
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
        Schema::dropIfExists('bs_order_type_of_work_inventories');
    }
}
