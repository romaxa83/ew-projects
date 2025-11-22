<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GpsCreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(DbConnections::GPS)
            ->create('history', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->timestamp('received_at');
                $table->bigInteger('truck_id')->nullable();
                $table->bigInteger('trailer_id')->nullable();
                $table->bigInteger('driver_id')->nullable();
                $table->string('longitude', 20)->nullable();
                $table->string('latitude', 20)->nullable();
                $table->double('speed', 10, 2)->nullable();
                $table->double('vehicle_mileage', 10, 2)->nullable();
                $table->double('heading', 10, 2)->nullable();
                $table->string('event_type', 20)->nullable();
                $table->integer('event_duration')->nullable();
                $table->integer('device_battery_level')->nullable();
                $table->boolean('device_battery_charging_status')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(DbConnections::GPS)
            ->dropIfExists('history');
    }
}
