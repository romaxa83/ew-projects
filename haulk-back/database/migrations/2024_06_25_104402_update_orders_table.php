<?php

use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('pickup_date_actual_tz')->nullable();
            $table->timestamp('delivery_date_actual_tz')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('pickup_date_actual_tz');
            $table->dropColumn('delivery_date_actual_tz');
        });
    }
};
