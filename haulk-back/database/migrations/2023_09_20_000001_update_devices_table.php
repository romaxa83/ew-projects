<?php

use App\Models\Saas\GPS\Device;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->string('status_request', 20)->nullable();
            $table->string('status_activate_request', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('status_request');
            $table->dropColumn('status_activate_request');
        });
    }
};

