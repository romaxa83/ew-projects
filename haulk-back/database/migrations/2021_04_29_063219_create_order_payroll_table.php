<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payroll', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('payroll_id')->index();
            $table->foreign('payroll_id')
                ->references('id')->on('payrolls')
                ->onDelete('cascade');

            $table->unsignedBigInteger('order_id')->index();
            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onDelete('cascade');

            $table->index(['payroll_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_payroll');
    }
}
