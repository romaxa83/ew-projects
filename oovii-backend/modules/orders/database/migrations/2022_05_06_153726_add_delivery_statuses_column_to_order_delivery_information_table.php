<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryStatusesColumnToOrderDeliveryInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'order_delivery_information',
            function (Blueprint $table) {
                $table->json('delivery_statuses')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(
            'order_delivery_information',
            function (Blueprint $table) {
                $table->dropColumn('delivery_statuses');
            }
        );
    }
}
