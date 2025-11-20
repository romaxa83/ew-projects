<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeReportsMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports_machines', function (Blueprint $table) {
            $table->string('header_brand')->nullable()->change();
            $table->string('header_model')->nullable()->change();
            $table->string('serial_number_header')->nullable()->change();
            $table->string('machine_serial_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('reports_machines', function (Blueprint $table) {
            $table->string('header_brand')->change();
            $table->string('header_model')->change();
            $table->string('serial_number_header')->change();
            $table->string('machine_serial_number')->change();
        });
    }
}