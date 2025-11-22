<?php

use App\Models\Orders;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->string('ecommerce_client_email')->nullable();
            $table->string('ecommerce_client_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('ecommerce_client_email');
            $table->dropColumn('ecommerce_client_name');
        });
    }
};

