<?php

use App\Enums\Faq\Questions\QuestionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'questions',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->enum('status', QuestionStatusEnum::getValues())->default(QuestionStatusEnum::NEW);

                $table->string('name');
                $table->string('email');
                $table->text('question');

                $table->text('answer')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
