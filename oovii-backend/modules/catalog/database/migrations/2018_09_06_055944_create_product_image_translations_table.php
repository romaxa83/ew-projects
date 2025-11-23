<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductImageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_image_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_image_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('alt')->nullable();
            $table->string('title')->nullable();

            $table->unique(['product_image_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_image_translations');
    }
}
