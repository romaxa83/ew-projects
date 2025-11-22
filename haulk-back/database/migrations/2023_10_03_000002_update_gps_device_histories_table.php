<?php

use App\Models\Saas\GPS\DeviceHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(DeviceHistory::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('context', 25);
        });
    }

    public function down(): void
    {
        Schema::table(DeviceHistory::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('context');
            $table->string('type', 25);
        });
    }
};






