<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostColumnToOrderDeliveryInformationTable extends Migration
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
                $table->float('delivery_cost')
                    ->after('tariff_code')
                    ->default(0.0);
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
                $table->dropColumn('delivery_cost');
            }
        );
    }
}
