<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPaymentsStructure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->tinyInteger('driver_payment_status')->nullable();
            $table->decimal('driver_received_amount', 10, 2)->nullable();
            $table->string('driver_not_received_comment')->nullable();
            $table->unsignedBigInteger('driver_cashless_method')->nullable();
            $table->decimal('driver_cashless_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('driver_cashless_timestamp')->nullable();
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
            $table->dropColumn('driver_payment_status');
            $table->dropColumn('driver_received_amount');
            $table->dropColumn('driver_not_received_comment');
            $table->dropColumn('driver_cashless_method');
            $table->dropColumn('driver_cashless_amount');
            $table->dropColumn('driver_cashless_timestamp');
        });
    }
}
