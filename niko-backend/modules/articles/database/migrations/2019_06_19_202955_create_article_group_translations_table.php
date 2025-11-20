<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleGroupTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_group_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->string('short_description')->nullable();
            $table->seo();

            $table->unique(['article_group_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_group_translations');
    }
}
