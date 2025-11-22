<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\Message;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(Message::TABLE_NAME, function (Blueprint $table) {
                $table->boolean('movement_status')->nullable();
                $table->integer('position_satellites')->nullable();
                $table->boolean('position_valid')->nullable();
                $table->timestamp('server_time_at')->nullable();
                $table->integer('gsm_signal_level')->nullable();
                $table->double('gps_fuel_rate',10, 2)->nullable();
                $table->double('gps_fuel_used',10, 2)->nullable();
                $table->double('external_powersource_voltage',10, 2)->nullable();
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(Message::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('movement_status');
                $table->dropColumn('position_satellites');
                $table->dropColumn('position_valid');
                $table->dropColumn('server_time_at');
                $table->dropColumn('gsm_signal_level');
                $table->dropColumn('gps_fuel_rate');
                $table->dropColumn('gps_fuel_used');
                $table->dropColumn('external_powersource_voltage');
            });
    }
};



