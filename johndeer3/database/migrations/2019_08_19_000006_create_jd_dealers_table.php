<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJdDealersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jd_dealers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('jd_id')->comment('Поле id из импорта');
            $table->string('jd_jd_id')->comment('Поле jd_id из импорта');
            $table->string('name');
            $table->string('country')->nullable();
            $table->boolean('status');
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
        Schema::dropIfExists('jd_dealers');
    }
}