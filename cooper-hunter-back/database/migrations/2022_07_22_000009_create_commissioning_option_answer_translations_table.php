<?php

use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\OptionAnswerTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(OptionAnswerTranslation::TABLE,
            static function (Blueprint $table)
            {
                $table->id();
                $table->text('text');

                $table->unsignedInteger('row_id');
                $table->foreign('row_id')
                    ->on(OptionAnswer::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OptionAnswerTranslation::TABLE);
    }
};


