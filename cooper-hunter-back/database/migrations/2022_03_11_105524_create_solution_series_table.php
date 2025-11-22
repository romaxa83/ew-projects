<?php

use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solution_series',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('slug')
                    ->unique();
                $table->unsignedInteger('sort')
                    ->default(SolutionSeries::DEFAULT_SORT);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_series');
    }
};
