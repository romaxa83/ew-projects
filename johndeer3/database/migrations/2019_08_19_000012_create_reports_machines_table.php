<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_machines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('equipment_group_id')->nullable()->unsigned();
            $table->foreign('equipment_group_id')->references('id')->on('jd_equipment_groups');
            $table->string('equipment_group')->nullable();
            $table->bigInteger('model_description_id')->nullable()->unsigned();
            $table->foreign('model_description_id')->references('id')->on('jd_model_descriptions');
            $table->string('model_description')->nullable();
            $table->string('trailed_equipment_type')->nullable();
            $table->string('header_brand');
            $table->string('header_model');
            $table->string('serial_number_header');
            $table->string('machine_serial_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports_machines');
    }
}