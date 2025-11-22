<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterQuestionsAnswersTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions_answers', function (Blueprint $table) {
            $table->string('question_ru')->nullable()->change();
            $table->string('question_es')->nullable()->change();
            $table->text('answer_ru')->nullable()->change();
            $table->text('answer_es')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions_answers', function (Blueprint $table) {
            $table->string('question_ru')->nullable(false)->change();
            $table->string('question_es')->nullable(false)->change();
            $table->text('answer_ru')->nullable(false)->change();
            $table->text('answer_es')->nullable(false)->change();
        });
    }
}
