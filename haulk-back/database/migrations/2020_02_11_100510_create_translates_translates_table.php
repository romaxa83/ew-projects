<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslatesTranslatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translates_translates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('text')->nullable();
            $table->unsignedBigInteger('row_id');
            $table->string('language', 3);
            $table->foreign('language')->references('slug')->on('languages')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('row_id')->references('id')->on('translates')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translates_translates', function (Blueprint $table) {
            $table->dropForeign(['language']);
            $table->dropForeign(['row_id']);
        });
        Schema::dropIfExists('translates_translates');
    }
}
