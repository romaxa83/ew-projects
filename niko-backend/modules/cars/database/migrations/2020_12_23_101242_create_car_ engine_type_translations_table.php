<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarEngineTypeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_engine_type_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('engine_type_id');
            $table->string('locale')->index();
            $table->string('name')->nullable();

            $table->unique(['engine_type_id', 'locale']);
            $table->foreign('engine_type_id')
                ->references('id')
                ->on('car_engine_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_engine_type_translations');
    }
}




