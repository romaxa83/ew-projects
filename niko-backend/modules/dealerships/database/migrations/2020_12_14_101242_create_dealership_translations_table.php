<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealershipTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealership_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dealership_id');
            $table->string('locale')->index();
            $table->string('name')->nullable();
            $table->text('text')->nullable();
            $table->string('address')->nullable();

            $table->unique(['dealership_id', 'locale']);
            $table->foreign('dealership_id')
                ->references('id')
                ->on('dealerships')
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
        Schema::dropIfExists('dealership_translations');
    }
}


