<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsFeatureValuesPivotTable extends Migration
{
    public function up()
    {
        Schema::table('reports_features_pivot', function (Blueprint $table) {
            $table->string('value_machine_2')->nullable()->comment('Значения для техники конкурента');
            $table->string('value_machine_3')->nullable()->comment('Значения для похожей техники');
        });
    }

    public function down()
    {
        Schema::table('reports_features_pivot', function (Blueprint $table) {
            $table->dropColumn('value_machine_2');
            $table->dropColumn('value_machine_3');
        });
    }
}
