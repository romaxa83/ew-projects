<?php

use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\Catalog\Solutions\Solution;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solution_default_line_sets',
            static function (Blueprint $table)
            {
                $table->id();
                $table->foreignId('indoor_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('line_set_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete();
                $table->enum('zone', SolutionZoneEnum::getValues());
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_default_line_sets');
    }
};
