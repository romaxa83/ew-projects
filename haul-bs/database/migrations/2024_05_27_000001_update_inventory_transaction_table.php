<?php

use App\Models\Inventories\Transaction;
use App\Models\Orders;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Transaction::TABLE, function (Blueprint $table) {
            $table->string('order_type', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Transaction::TABLE, function (Blueprint $table) {
            $table->dropColumn('order_type');
        });
    }
};
