<?php

use App\Models\Catalog\Solutions\Solution;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solution_schemas',
            static function (Blueprint $table)
            {
                $table->foreignId('outdoor_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('indoor_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete();
                $table->unsignedInteger('zone');
                $table->unsignedInteger('count_zones');

                $table->primary(
                    [
                        'outdoor_id',
                        'indoor_id',
                        'zone',
                        'count_zones'
                    ],
                    'pk'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_schemas');
    }
};
