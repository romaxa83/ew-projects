<?php

use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(OptionAnswer::TABLE,
            static function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('sort')->default(0);

                $table->unsignedInteger('question_id');
                $table->foreign('question_id')
                    ->on(Question::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OptionAnswer::TABLE);
    }
};



