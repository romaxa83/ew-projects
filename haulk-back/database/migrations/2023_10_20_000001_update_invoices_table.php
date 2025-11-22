<?php

use App\Models\Billing\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->json('gps_device_payment_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('gps_device_payment_data');
        });
    }
};









