<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureValueTranslatesTable extends Migration
{
    public function up()
    {
        Schema::create('feature_value_translates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang', 10);
            $table->string('name');
            $table->unsignedBigInteger('value_id');
            $table->unique(['value_id', 'lang']);
            $table->foreign('value_id')
                ->references('id')
                ->on('feature_values')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feature_value_translates');
    }
}
