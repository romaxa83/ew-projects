<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model',250)->comment('Тип записи (к примеру notification)');
            $table->string('entity_type', 250)->comment('К какой модели относиться запись (к примеру user)');
            $table->integer('entity_id')->comment('К какой модели относиться запись (к примеру notification)');
            $table->string('url');
            $table->text('basename');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}