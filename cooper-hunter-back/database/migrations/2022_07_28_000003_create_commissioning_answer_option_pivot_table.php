<?php

use App\Models\Commercial\Commissioning\Answer;
use App\Models\Commercial\Commissioning\AnswerOptionPivot;
use App\Models\Commercial\Commissioning\OptionAnswer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AnswerOptionPivot::TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('answer_id');
            $table->foreign('answer_id')
                ->references('id')
                ->on(Answer::TABLE)
                ->onDelete('cascade');

            $table->unsignedInteger('option_answer_id');
            $table->foreign('option_answer_id')
                ->references('id')
                ->on(OptionAnswer::TABLE)
                ->onDelete('cascade');

            $table->primary(['answer_id', 'option_answer_id'], 'pk-aaop_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(AnswerOptionPivot::TABLE);
    }
};
