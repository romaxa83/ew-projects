<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableActiveDriverHistoriesChangeAmountType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('active_driver_histories', function (Blueprint $table) {
            $table->decimal('amount', 10, 4)
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('active_driver_histories', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)
                ->nullable()
                ->change();
        });
    }
}
