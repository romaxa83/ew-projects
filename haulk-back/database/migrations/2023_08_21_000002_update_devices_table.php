<?php

use App\Models\Saas\GPS\Device;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->bigInteger('flespi_device_id')
                ->index()
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('flespi_device_id');
        });
    }
};

