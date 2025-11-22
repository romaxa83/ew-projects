<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverAgeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_age_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('driver_age_id');

            $table->unique(['driver_age_id', 'lang']);
            $table->foreign('driver_age_id')
                ->references('id')
                ->on('driver_ages')
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
        Schema::dropIfExists('driver_age_translations');
    }
}
