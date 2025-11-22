<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GpsCreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(DbConnections::GPS)
            ->create('alerts', function (Blueprint $table) {
                $table->id();
                $table->timestamp('received_at');
                $table->bigInteger('truck_id')->nullable();
                $table->bigInteger('trailer_id')->nullable();
                $table->bigInteger('driver_id')->nullable();
                $table->string('longitude', 20)->nullable();
                $table->string('latitude', 20)->nullable();
                $table->double('speed', 3, 2)->nullable();
                $table->string('alert_type', 20);
                $table->string('alert_subtype', 20)->nullable();
                $table->foreignId('history_id')
                    ->nullable()
                    ->references('id')->on('history')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                }
            );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(DbConnections::GPS)
            ->dropIfExists('alerts');
    }
}
