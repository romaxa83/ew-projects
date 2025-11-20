<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportReportClientPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_report_client', function (Blueprint $table) {
            $table->bigInteger('report_id')->unsigned();
            $table->foreign('report_id')->references('id')->on('reports')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('report_client_id')->unsigned();
            $table->foreign('report_client_id')->references('id')->on('reports_clients')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['report_id', 'report_client_id']);
            $table->smallInteger('type')->nullable()->comment('Тип клиента (конкурент/потенциальный)');
            $table->string('name_machine')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_report_client');
    }
}