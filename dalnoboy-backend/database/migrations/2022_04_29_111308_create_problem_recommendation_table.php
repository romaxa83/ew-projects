<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'problem_recommendation',
            static function (Blueprint $table) {
                $table->foreignId('recommendation_id')
                    ->constrained('recommendations')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('problem_id')
                    ->constrained('problems')
                    ->references('id')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_recommendation');
    }
};
