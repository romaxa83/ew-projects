<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('title_en');
            $table->string('title_ru');
            $table->string('title_es');
            $table->text('body_short_en');
            $table->text('body_short_ru');
            $table->text('body_short_es');
            $table->mediumText('body_en');
            $table->mediumText('body_ru');
            $table->mediumText('body_es');
            $table->boolean('sticky')->default(false);
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
