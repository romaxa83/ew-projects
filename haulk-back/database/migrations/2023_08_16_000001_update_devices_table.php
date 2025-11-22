<?php

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->string('status', 10)
                ->after('id')
                ->default(DeviceStatus::ACTIVE);
            $table->string('phone')
                ->after('status')
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('phone');
        });
    }
};

