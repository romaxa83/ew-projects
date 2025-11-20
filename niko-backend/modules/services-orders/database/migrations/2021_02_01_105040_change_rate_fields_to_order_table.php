<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRateFieldsToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->integer('rating_services')->nullable()->change();
            $table->integer('rating_order')->nullable()->change();

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
            $table->integer('rating_services')->default(0)->change()->comment('Оценка обслуживания авто');
            $table->integer('rating_order')->default(0)->change()->comment('Оценка записи на обслуживание обслуживания авто');

        });
    }
}

