<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'recommendation_regulation',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('recommendation_id')
                    ->constrained('recommendations')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->foreignId('regulation_id')
                    ->constrained('regulations')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_regulation');
    }
};
