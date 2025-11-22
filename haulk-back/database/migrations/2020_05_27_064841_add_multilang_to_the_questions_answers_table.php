<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultilangToTheQuestionsAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions_answers', function (Blueprint $table) {
            $table->string('question_ru')
                ->after('question');
            $table->string('question_es')
                ->after('question');
            $table->text('answer_ru')
                ->after('answer');
             $table->text('answer_es')
                 ->after('answer');
             $table->renameColumn('question', 'question_en');
             $table->renameColumn('answer', 'answer_en');
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
            $table->dropColumn('question_ru');
            $table->dropColumn('question_es');
            $table->dropColumn('answer_ru');
            $table->dropColumn('answer_es');
            $table->renameColumn('question_en', 'question');
            $table->renameColumn('answer_en', 'answer');
        });
    }
}
