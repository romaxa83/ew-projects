<?php

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Models\Catalog\Solutions\Solution;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solution_climate_zones',
            static function (Blueprint $table)
            {
                $table->foreignId('solution_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->enum(
                    'climate_zone',
                    SolutionClimateZoneEnum::getValues()
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_climate_zones');
    }
};
