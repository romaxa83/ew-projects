<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJdManufacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jd_manufacturers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('jd_id')->comment('Поле id из импорта');
            $table->string('name');
            $table->boolean('status');
            $table->tinyInteger('is_partner_jd')->comment('Партнер ли jd');
            $table->integer('position');
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
        Schema::dropIfExists('jd_manufacturers');
    }
}
