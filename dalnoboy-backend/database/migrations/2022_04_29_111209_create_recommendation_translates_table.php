<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'recommendation_translates',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title');

                $table->foreignId('row_id')
                    ->constrained('recommendations')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->index(['row_id', 'language']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_translates');
    }
};
