<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBsInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bs_inventories', function (Blueprint $table) {
            $table->float('quantity')->change();
            $table->foreignId('unit_id')
                ->index()
                ->references('id')->on('bs_inventory_units')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bs_inventories', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->dropColumn('unit_id');
        });
    }
}
