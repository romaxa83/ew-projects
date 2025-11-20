<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureValuesPivotTable extends Migration
{
    public function up()
    {
        Schema::create('reports_features_pivot', function (Blueprint $table) {
            $table->bigInteger('report_id')->unsigned();
            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->bigInteger('feature_id')->unsigned();
            $table->foreign('feature_id')
                ->references('id')
                ->on('reports_features')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('value');
            $table->primary(['report_id', 'feature_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports_features_pivot');
    }
}
