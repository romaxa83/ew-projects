<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GpsCreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(DbConnections::GPS)
            ->create('messages', function (Blueprint $table) {
                $table->id();
                $table->timestamp('received_at');
                $table->string('imei', 15);
                $table->string('longitude', 20)->nullable();
                $table->string('latitude', 20)->nullable();
                $table->double('speed', 3, 2)->nullable();
                $table->double('heading', 3, 2)->nullable();
                $table->double('vehicle_mileage', 10, 2)->nullable();
                $table->boolean('driving')->nullable();
                $table->boolean('idling')->nullable();
                $table->boolean('engine_off')->nullable();
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
            ->dropIfExists('messages');
    }
}
