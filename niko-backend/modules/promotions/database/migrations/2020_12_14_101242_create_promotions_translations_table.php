<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promotions_id');
            $table->string('locale')->index();
            $table->string('name')->nullable();
            $table->text('text')->nullable();

            $table->unique(['promotions_id', 'locale']);
            $table->foreign('promotions_id')
                ->references('id')
                ->on('promotions')
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
        Schema::dropIfExists('promotions_translations');
    }
}


