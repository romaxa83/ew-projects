<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureValueTable extends Migration
{
    public function up()
    {
        Schema::create('feature_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feature_id');
            $table->foreign('feature_id')
                ->references('id')
                ->on('reports_features')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feature_values');
    }
}
