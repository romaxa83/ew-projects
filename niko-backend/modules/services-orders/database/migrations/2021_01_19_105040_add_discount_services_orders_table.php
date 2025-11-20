<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountServicesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->integer('spare_parts_discount')->default(0)
                ->comment('Скидка на запчасти по докуметну реализации');
            $table->integer('service_discount')->default(0)
                ->comment('Скидка на работы по докуметну реализации');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->dropColumn('spare_parts_discount');
            $table->dropColumn('service_discount');
        });
    }
}
