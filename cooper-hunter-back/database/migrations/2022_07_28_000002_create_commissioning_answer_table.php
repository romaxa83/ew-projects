<?php

use App\Models\Commercial\Commissioning\Answer;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Answer::TABLE,
            static function (Blueprint $table)
            {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('project_protocol_question_id');
                $table->foreign('project_protocol_question_id')
                    ->on(ProjectProtocolQuestion::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->text('text')->nullable();
                $table->boolean('radio')->default(false);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Answer::TABLE);
    }
};




