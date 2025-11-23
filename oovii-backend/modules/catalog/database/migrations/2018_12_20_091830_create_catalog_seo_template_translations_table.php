<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogSeoTemplateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_seo_template_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_seo_template_id');
            $table->string('locale')->index();
            $table->seo();
            $table->mediumText('text')->nullable();

            $table->unique(['catalog_seo_template_id', 'locale'], 'catalog_seo_tpl_unique');
            $table->foreign('catalog_seo_template_id', 'catalog_seo_tpl_translations_catalog_seo_tpl_id_foreign')
                ->references('id')
                ->on('catalog_seo_templates')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_seo_template_translations');
    }
}
