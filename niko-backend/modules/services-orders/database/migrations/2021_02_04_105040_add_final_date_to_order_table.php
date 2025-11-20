<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalDateToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->dateTime('final_date')->nullable()->after('on_date')->comment('Дата , окончательная, на которую назначена заявка');
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
            $table->dropColumn('rate_date');
        });
    }
}
