<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureTable extends Migration
{
    public function up()
    {
        Schema::create('reports_features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('type');
            $table->string('type_field');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports_features');
    }
}
