<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeReportsFeaturesTable extends Migration
{
    public function up()
    {
        Schema::table('reports_features', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('unit')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reports_features', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('unit');
        });
    }
}
