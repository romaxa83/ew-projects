<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransmissionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transmission_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('transmission_id');

            $table->unique(['transmission_id', 'lang']);
            $table->foreign('transmission_id')
                ->references('id')
                ->on('transmissions')
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
        Schema::dropIfExists('transmission_translations');
    }
}
