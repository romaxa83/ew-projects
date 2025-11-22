<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGpsDeviceIdColumnToTrailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->foreignId('gps_device_id')
                ->nullable()
                ->references('id')->on('gps_devices')
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
        Schema::table('trailers', function (Blueprint $table) {
            $table->dropColumn('gps_device_id');
        });
    }
}
