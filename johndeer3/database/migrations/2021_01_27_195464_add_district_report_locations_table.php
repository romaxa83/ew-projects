<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistrictReportLocationsTable extends Migration
{
    public function up()
    {
        Schema::table('reports_locations', function (Blueprint $table) {
            $table->string('district')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reports_locations', function (Blueprint $table) {
            $table->dropColumn('district');
        });
    }
}
