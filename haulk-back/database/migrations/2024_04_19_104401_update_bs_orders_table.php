<?php

use App\Models\BodyShop\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->decimal('parts_cost')->nullable();
            $table->decimal('profit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('parts_cost');
            $table->dropColumn('profit');
        });
    }
};
