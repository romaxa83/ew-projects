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
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->boolean('with_tax_exemption')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_terms');
            $table->dropColumn('with_tax_exemption');
        });
    }
};
