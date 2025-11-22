<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverReportPayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_report_payroll', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('payroll_id')->index();
            $table->foreign('payroll_id')
                ->references('id')->on('payrolls')
                ->onDelete('cascade');

            $table->unsignedBigInteger('driver_report_id')->index();
            $table->foreign('driver_report_id')
                ->references('id')->on('driver_reports')
                ->onDelete('cascade');

            $table->index(['payroll_id', 'driver_report_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_report_payroll');
    }
}
