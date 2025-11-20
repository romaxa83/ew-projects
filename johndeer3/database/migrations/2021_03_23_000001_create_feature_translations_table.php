<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang');
            $table->string('name');
            $table->string('unit')->nullable();

            $table->bigInteger('feature_id')->unsigned();
            $table->foreign('feature_id')
                ->references('id')
                ->on('reports_features')
                ->onDelete('cascade');

            $table->unique(['feature_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feature_translations');
    }
}
