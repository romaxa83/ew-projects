<?php

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(ProjectProtocolQuestion::TABLE,
            static function (Blueprint $table)
            {
                $table->bigIncrements('id');

                $table->unsignedInteger('question_id');
                $table->foreign('question_id')
                    ->on(Question::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedBigInteger('project_protocol_id');
                $table->foreign('project_protocol_id', 'fk-project-protocol_question')
                    ->on(ProjectProtocol::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('answer_status', 10)->default(AnswerStatus::NONE);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(ProjectProtocolQuestion::TABLE);
    }
};




