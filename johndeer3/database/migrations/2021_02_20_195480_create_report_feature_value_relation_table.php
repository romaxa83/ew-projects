<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportFeatureValueRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_feature_value_relation', function (Blueprint $table) {
            $table->bigInteger('report_id')->unsigned();
            $table->foreign('report_id')
                ->references('id')
                ->on('reports');

            $table->bigInteger('feature_id')->unsigned();
            $table->foreign('feature_id')
                ->references('id')
                ->on('reports_features');

            $table->bigInteger('value_id')->unsigned();
            $table->foreign('value_id')
                ->references('id')
                ->on('report_feature_values')
                ->onDelete('cascade');

            $table->primary(['feature_id', 'report_id', 'value_id'], 'prk_report-feature-value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_feature_value_relation');
    }
}

