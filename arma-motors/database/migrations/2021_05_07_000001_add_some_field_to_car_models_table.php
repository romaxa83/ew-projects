<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldToCarModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->boolean('for_credit')->default(false)->comment('Выводить для кредитования');
            $table->boolean('for_calc')->default(false)->comment('Выводить для калькуляции');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->dropColumn('for_credit');
            $table->dropColumn('for_calc');
        });
    }
}

