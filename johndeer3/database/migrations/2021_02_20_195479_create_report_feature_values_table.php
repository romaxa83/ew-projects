<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportFeatureValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_feature_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('value')->nullable();
            $table->string('model_description_name')->nullable();

            $table->bigInteger('model_description_id')
                ->nullable()->unsigned();
            $table->foreign('model_description_id')
                ->references('id')
                ->on('jd_model_descriptions');

            $table->bigInteger('value_id')
                ->nullable()->unsigned();
            $table->foreign('value_id')
                ->references('id')
                ->on('feature_values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_feature_values');
    }
}
