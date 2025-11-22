<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 350);
            $table->integer('entity_id');
            $table->string('type')->nullable();
            $table->string('model')->nullable();
            $table->text('basename');
            $table->string('hash');
            $table->boolean('active')->default(true);
            $table->integer('position')->default(0);
            $table->string('mime')->nullable();
            $table->string('ext')->nullable();
            $table->string('size')->nullable();
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
        Schema::dropIfExists('files');
    }
}
