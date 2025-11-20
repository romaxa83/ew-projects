<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportJdClientPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_report', function (Blueprint $table) {
            $table->bigInteger('report_id')->unsigned();
            $table->foreign('report_id')->references('id')->on('reports')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('jd_clients')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['report_id', 'client_id']);
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
        Schema::dropIfExists('client_report');
    }
}