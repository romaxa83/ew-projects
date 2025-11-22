<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'inspection_tires',
            static function (Blueprint $table)
            {
                $table->id();
                $table->foreignId('inspection_id')
                    ->constrained('inspections')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->foreignId('tire_id')
                    ->constrained('tires')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->foreignId('schema_wheel_id')
                    ->constrained('schema_wheels')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->float('ogp');
                $table->float('pressure');
                $table->text('comment')
                    ->nullable();
                $table->boolean('no_problems')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_tires');
    }
};
