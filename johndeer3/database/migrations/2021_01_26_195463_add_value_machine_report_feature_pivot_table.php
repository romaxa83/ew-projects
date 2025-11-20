<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueMachineReportFeaturePivotTable extends Migration
{
    public function up()
    {
        Schema::table('reports_features_pivot', function (Blueprint $table) {
            $table->string('value_machine_4')->nullable();
            $table->string('value_machine_5')->nullable();
            $table->string('value_machine_6')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reports_features_pivot', function (Blueprint $table) {
            $table->dropColumn('value_machine_4');
            $table->dropColumn('value_machine_5');
            $table->dropColumn('value_machine_6');
        });
    }
}
