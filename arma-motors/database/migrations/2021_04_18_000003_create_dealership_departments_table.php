<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealershipDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealership_departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dealership_id');
            $table->foreign('dealership_id')
                ->references('id')
                ->on('dealerships')
                ->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->integer('sort')->default(0);
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('telegram')->nullable();
            $table->string('viber')->nullable();
            $table->tinyInteger('type');
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
        Schema::dropIfExists('dealership_departments');
    }
}
