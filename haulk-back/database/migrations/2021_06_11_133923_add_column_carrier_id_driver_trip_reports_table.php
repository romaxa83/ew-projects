<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCarrierIdDriverTripReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_trip_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('carrier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_trip_reports', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });
    }
}
