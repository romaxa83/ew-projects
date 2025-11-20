<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorServicesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->dropColumn('spare_parts_discount');
            $table->dropColumn('service_discount');
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
            $table->float('spare_parts_discount')->default(0)
                ->comment('Скидка на запчасти по докуметну реализации');
            $table->float('service_discount')->default(0)
                ->comment('Скидка на работы по докуметну реализации');
        });
    }
}
