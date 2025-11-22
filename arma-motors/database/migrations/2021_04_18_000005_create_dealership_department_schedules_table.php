<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealershipDepartmentSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealership_department_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('department_id');
            $table->string('day',20);
            $table->time('work_start')->nullable();
            $table->time('work_end')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            $table->foreign('department_id')
                ->references('id')
                ->on('dealership_departments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealership_department_schedules');
    }
}
