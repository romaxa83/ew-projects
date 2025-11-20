<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportReportMachinePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_report_machine', function (Blueprint $table) {
            $table->bigInteger('report_id')->unsigned();
            $table->foreign('report_id')->references('id')->on('reports')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('report_machine_id')->unsigned();
            $table->foreign('report_machine_id')->references('id')->on('reports_machines')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['report_id', 'report_machine_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_report_machine');
    }
}