<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteReportsFeaturesTranslateTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('reports_features_translates');
    }

    public function down()
    {
        Schema::create('reports_features_translates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('row_id')->unsigned();
            $table->foreign('row_id')
                ->references('id')
                ->on('reports_features')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('lang',10);
            $table->string('name');
            $table->string('unit')->nullable();
        });
    }
}
