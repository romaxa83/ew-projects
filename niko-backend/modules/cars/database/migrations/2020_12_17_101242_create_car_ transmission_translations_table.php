<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarTransmissionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_transmission_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('transmission_id');
            $table->string('locale')->index();
            $table->string('name')->nullable();

            $table->unique(['transmission_id', 'locale']);
            $table->foreign('transmission_id')
                ->references('id')
                ->on('car_transmissions')
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
        Schema::dropIfExists('car_transmission_translations');
    }
}



