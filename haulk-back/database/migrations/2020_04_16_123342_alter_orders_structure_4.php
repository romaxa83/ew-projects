<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersStructure4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('buyer_number');
            $table->dropColumn('lot_number');

            $table->string('pickup_buyer_name_number')->nullable();
            $table->unsignedBigInteger('pickup_date_actual')->nullable();
            $table->json('pickup_time')->nullable();
            $table->string('delivery_buyer_number')->nullable();
            $table->unsignedBigInteger('delivery_date_actual')->nullable();
            $table->json('delivery_time')->nullable();
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
            $table->string('buyer_number')->nullable();
            $table->string('lot_number')->nullable();

            $table->dropColumn('pickup_buyer_name_number');
            $table->dropColumn('pickup_date_actual');
            $table->dropColumn('pickup_time');
            $table->dropColumn('delivery_buyer_number');
            $table->dropColumn('delivery_date_actual');
            $table->dropColumn('delivery_time');
        });
    }
}
