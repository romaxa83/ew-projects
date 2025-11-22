<?php

use App\Models\Saas\GPS\Device;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->string('company_device_name', 500)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('company_device_name');
        });
    }
};
