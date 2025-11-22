<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersStructure5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('pickup_customer_not_available')->default(false);
            $table->string('pickup_customer_full_name')->nullable();
            $table->boolean('delivery_customer_not_available')->default(false);
            $table->string('delivery_customer_full_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pickup_customer_not_available');
            $table->dropColumn('pickup_customer_full_name');
            $table->dropColumn('delivery_customer_not_available');
            $table->dropColumn('delivery_customer_full_name');
        });
    }
}
