<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageTranslatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('page_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang', 10);
            $table->string('name')->nullable();
            $table->longText('text')->nullable();
            $table->unsignedBigInteger('page_id');
            $table->unique(['page_id', 'lang']);
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_translations');
    }
}

