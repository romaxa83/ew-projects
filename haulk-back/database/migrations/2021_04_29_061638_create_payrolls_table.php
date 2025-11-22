<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('carrier_id')->index('carrier_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedTinyInteger('driver_rate');
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('salary', 10, 2)->default(0);
            $table->json('order_expenses')->nullable();
            $table->json('expenses_before')->nullable();
            $table->json('expenses_after')->nullable();
            $table->json('bonuses')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}
