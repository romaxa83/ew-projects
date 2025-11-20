<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsReportsTable extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('machine_for_compare')->nullable()->comment('Название техники для сравнения');
        });
    }

    public function down()
    {
        Schema::table('reports_features_pivot', function (Blueprint $table) {
            $table->dropColumn('machine_for_compare');
        });
    }
}
