<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslatesTable extends Migration
{

    public function up(): void
    {
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->string('place');
            $table->string('key');
            $table->string('text')->nullable();
            $table->timestamps();

            $table->string('lang', 4);
            $table->foreign('lang')
                ->references('slug')
                ->on('languages')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['place', 'key', 'lang']);
        });
    }

    public function down(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->dropForeign(['lang']);
            $table->dropUnique(['place', 'key', 'lang']);
        });
        Schema::dropIfExists('translates');
    }
}
