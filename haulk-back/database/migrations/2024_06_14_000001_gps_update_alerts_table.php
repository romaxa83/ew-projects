<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\Alert::TABLE_NAME, function (Blueprint $table) {
                $table->string('min_lost_connection')->nullable();
            });

        \App\Models\GPS\Alert::query()
            ->whereIn('alert_type',[
                \App\Models\GPS\Alert::ALERT_DEVICE_CONNECTION_LOST,
                \App\Models\GPS\Alert::ALERT_TYPE_DEVICE_CONNECTION,
            ])
            ->update(['min_lost_connection' => '10']);
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\Alert::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('min_lost_connection');
            });
    }
};
