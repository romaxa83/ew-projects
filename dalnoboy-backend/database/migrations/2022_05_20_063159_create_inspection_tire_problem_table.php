<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'inspection_tire_problem',
            static function (Blueprint $table)
            {
                $table->foreignId('inspection_tire_id')
                    ->constrained('inspection_tires')
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
        Schema::dropIfExists('inspection_tire_problem');
    }
};
