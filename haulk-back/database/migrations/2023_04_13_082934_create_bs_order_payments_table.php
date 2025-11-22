<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateBsOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bs_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->references('id')->on('bs_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->decimal('amount');
            $table->dateTime('payment_date');
            $table->string('payment_method');
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bs_order_payments');
    }
}
