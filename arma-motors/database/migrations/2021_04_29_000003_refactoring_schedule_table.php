<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactoringScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealership_department_schedules', function (Blueprint $table) {
            $table->integer('day')->change();
            $table->dropColumn('work_start');
            $table->dropColumn('work_end');
            $table->dropColumn('break_start');
            $table->dropColumn('break_end');
            $table->integer('from')->nullable();
            $table->integer('to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealership_department_schedules', function (Blueprint $table) {
            $table->string('day',20)->change();
            $table->time('work_start')->nullable();
            $table->time('work_end')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->dropColumn('from');
            $table->dropColumn('to');
        });
    }
}

