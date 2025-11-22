<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportTypeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_type_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('transport_type_id');

            $table->unique(['transport_type_id', 'lang']);
            $table->foreign('transport_type_id')
                ->references('id')
                ->on('transport_types')
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
        Schema::dropIfExists('transport_type_translations');
    }
}
