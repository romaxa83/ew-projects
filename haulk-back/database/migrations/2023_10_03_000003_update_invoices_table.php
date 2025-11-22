<?php

use App\Models\Billing\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->boolean('has_gps_subscription')->default(false);
            $table->decimal('gps_device_amount', 10, 2)->default(0);
            $table->json('gps_device_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('has_gps_subscription');
            $table->dropColumn('gps_device_amount');
            $table->dropColumn('gps_device_data');
        });
    }
};







