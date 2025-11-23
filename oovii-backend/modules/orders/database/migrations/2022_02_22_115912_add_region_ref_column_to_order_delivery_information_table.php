<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionRefColumnToOrderDeliveryInformationTable extends Migration
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
                $table->string('region_code')->after('order_id')->nullable();
                $table->string('postal_code')->after('branch_ref')->nullable();
                $table->string('address')->after('postal_code')->nullable();
                $table->renameColumn('city_ref', 'city_code');
                $table->renameColumn('branch_ref', 'branch_code');
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
                $table->dropColumn('region_code');
                $table->dropColumn('postal_code');
                $table->dropColumn('address');
                $table->renameColumn('city_code', 'city_ref');
                $table->renameColumn('branch_code', 'branch_ref');
            }
        );
    }
}
