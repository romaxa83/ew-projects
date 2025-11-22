<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceIsPaidWithPaidAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_at')->nullable();
        });

        DB::statement('
            update orders
            set paid_at = payments.receipt_date
            from payments
            where payments.order_id = orders.id
              and payments.receipt_date is not null
              and orders.is_paid = true
        ');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('receipt_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropColumn('old_values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false);
            $table->json('old_values')->nullable();
        });

        DB::statement('
            update orders
            set is_paid = true
            where orders.paid_at is not null
        ');

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('receipt_date')->nullable();
        });

        DB::statement('
            update payments
            set receipt_date = orders.paid_at
            from orders
            where payments.order_id = orders.id
              and orders.paid_at is not null
        ');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
}
