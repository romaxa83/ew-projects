<?php

use App\Models\Catalog\Solutions\Solution;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solution_pivot',
            static function (Blueprint $table)
            {
                $table->foreignId('parent_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreignId('child_id')
                    ->constrained(Solution::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_pivot');
    }
};
