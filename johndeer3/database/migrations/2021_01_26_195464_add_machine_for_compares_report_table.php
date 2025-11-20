<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMachineForComparesReportTable extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('machine_for_compare_4')->nullable();
            $table->string('machine_for_compare_5')->nullable();
            $table->string('machine_for_compare_6')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('machine_for_compare_4');
            $table->dropColumn('machine_for_compare_5');
            $table->dropColumn('machine_for_compare_6');
        });
    }
}
