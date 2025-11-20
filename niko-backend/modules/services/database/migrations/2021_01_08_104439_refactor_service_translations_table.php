<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorServiceTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->dropUnique('service_translations_slug_locale_unique');
            $table->dropColumn('slug');
            $table->dropColumn('h1');
            $table->dropColumn('title');
            $table->dropColumn('keywords');
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->string('slug');
            $table->seo();

            $table->unique(['slug', 'locale']);
        });
    }
}
