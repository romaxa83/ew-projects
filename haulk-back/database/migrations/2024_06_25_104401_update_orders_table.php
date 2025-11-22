<?php

use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->json('pickup_date_data')
                ->after('pickup_date_actual')->nullable();
            $table->json('delivery_date_data')
                ->after('delivery_date_actual')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('pickup_date_data');
            $table->dropColumn('delivery_date_data');
        });
    }
};
