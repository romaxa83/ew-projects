<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureEgPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_features_eg_pivot', function (Blueprint $table) {
            $table->bigInteger('feature_id')->unsigned();
            $table->foreign('feature_id')
                ->references('id')
                ->on('reports_features')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->bigInteger('eg_id')->unsigned();
            $table->foreign('eg_id')
                ->references('id')
                ->on('jd_equipment_groups')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary(['feature_id', 'eg_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_features_eg_pivot');
    }
}
