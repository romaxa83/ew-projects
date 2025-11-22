<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'inspection_tire_recommendation',
            static function (Blueprint $table)
            {
                $table->foreignId('inspection_tire_id')
                    ->constrained('inspection_tires')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->foreignId('recommendation_id')
                    ->constrained('recommendations')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->foreignId('new_tire_id')
                    ->nullable()
                    ->constrained('tires')
                    ->references('id')
                    ->nullOnDelete();

                $table->boolean('is_confirmed');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_tire_recommendation');
    }
};
