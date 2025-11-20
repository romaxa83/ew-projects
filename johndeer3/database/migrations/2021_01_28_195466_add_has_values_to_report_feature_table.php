<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasValuesToReportFeatureTable extends Migration
{
    public function up()
    {
        Schema::table('reports_features', function (Blueprint $table) {
            $table->boolean('has_value')->default(false);
        });
    }

    public function down()
    {
        Schema::table('reports_features', function (Blueprint $table) {
            $table->dropColumn('has_value');
        });
    }
}
