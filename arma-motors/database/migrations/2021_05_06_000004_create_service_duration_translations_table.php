<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceDurationTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_duration_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('duration_id');

            $table->unique(['duration_id', 'lang']);
            $table->foreign('duration_id')
                ->references('id')
                ->on('service_durations')
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
        Schema::dropIfExists('service_duration_translations');
    }
}
