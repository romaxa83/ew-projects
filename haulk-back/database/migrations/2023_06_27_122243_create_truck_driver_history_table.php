<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTruckDriverHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('truck_driver_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('truck_id')
                ->references('id')->on('trucks')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('truck_driver_history');
    }
}
