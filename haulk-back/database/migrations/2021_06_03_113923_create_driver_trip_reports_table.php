<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverTripReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_trip_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('driver_id');
            $table->timestamp('report_date');
            $table->timestamp('date_from');
            $table->timestamp('date_to');

            $table->index('driver_id');
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
            $table->dropIndex(['driver_id']);
        });
        Schema::dropIfExists('driver_trip_reports');
    }
}
