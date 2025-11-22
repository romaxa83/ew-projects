<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('method_id')->nullable();
            $table->string('terms')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('driver_pay', 10, 2)->nullable();
            $table->decimal('broker_fee', 10, 2)->nullable();
            $table->string('invoice_id')->nullable();
            $table->string('invoice_notes')->nullable();

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('payments');
    }
}
