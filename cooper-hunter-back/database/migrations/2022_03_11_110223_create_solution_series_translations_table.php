<?php

use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solution_series_translations',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('title');
                $table->string('language');

                $table->foreignId('row_id')
                    ->constrained(SolutionSeries::TABLE)
                    ->references('id')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_series_translations');
    }
};
