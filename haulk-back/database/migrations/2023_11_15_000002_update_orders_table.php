<?php

use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('driver_pickup_id')
                ->after('driver_id')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('driver_pickup_id');
        });
    }
};
