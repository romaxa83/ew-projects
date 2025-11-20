<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSubToReportFeatureValueReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_feature_value_relation', function (Blueprint $table) {
            $table->boolean('is_sub')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_feature_value_relation', function (Blueprint $table) {
            $table->dropColumn('is_sub');
        });
    }
}

