<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForStatisticToJdEquipmentGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jd_equipment_groups', function (Blueprint $table) {
            $table->boolean('for_statistic')->default(false)->comment('выводить ли группу в фильтре по статистике');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jd_equipment_groups', function (Blueprint $table) {
            $table->dropColumn('for_statistic');
        });
    }
}
