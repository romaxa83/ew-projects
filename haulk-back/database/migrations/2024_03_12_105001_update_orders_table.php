<?php

use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->boolean('is_manual_change_to_pickup')->default(false);
            $table->boolean('is_manual_change_to_delivery')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn([
                'is_manual_change_to_pickup',
                'is_manual_change_to_delivery'
            ]);
        });
    }
};
