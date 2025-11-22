<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterVehiclesStructure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('pickup_inspection_id')->nullable();
            $table->unsignedBigInteger('delivery_inspection_id')->nullable();

            $table->foreign('pickup_inspection_id')
                ->references('id')->on('inspections')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('delivery_inspection_id')
                ->references('id')->on('inspections')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['pickup_inspection_id']);
            $table->dropForeign(['delivery_inspection_id']);
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('pickup_inspection_id');
            $table->dropColumn('delivery_inspection_id');
        });
    }
}
