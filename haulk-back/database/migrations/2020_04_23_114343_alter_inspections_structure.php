<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInspectionsStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable();
        });
        Schema::table('inspections', function (Blueprint $table) {
            $table->foreign('vehicle_id')
                ->references('id')->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
}
