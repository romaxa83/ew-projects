<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'systems',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('name');
                $table->string('description', 1000)->nullable();
                $table->nullableTimestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};
