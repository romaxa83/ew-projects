<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'tire_sizes',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('tire_proportionality_id')
                    ->index()
                    ->constrained('tire_proportionalities')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->foreignId('tire_height_id')
                    ->index()
                    ->constrained('tire_heights')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->foreignId('tire_diameter_id')
                    ->index()
                    ->constrained('tire_diameters')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->unsignedBigInteger('order_column')->index();
                $table->boolean('active')->default(true);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tire_sizes');
    }
};
