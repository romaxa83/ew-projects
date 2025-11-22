<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang')->index();
            $table->string('name')->nullable();
            $table->string('text')->nullable();
            $table->unsignedBigInteger('model_id');

            $table->unique(['model_id', 'lang']);
            $table->foreign('model_id')
                ->references('id')
                ->on('promotions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_translations');
    }
}

