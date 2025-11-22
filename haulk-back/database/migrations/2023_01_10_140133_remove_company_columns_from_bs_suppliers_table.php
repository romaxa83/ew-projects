<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCompanyColumnsFromBsSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bs_suppliers', function (Blueprint $table) {
            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bs_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->index('broker_id');
            $table->index('carrier_id');
        });
    }
}
