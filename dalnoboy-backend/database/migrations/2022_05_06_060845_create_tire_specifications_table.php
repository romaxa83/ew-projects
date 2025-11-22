<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'tire_specifications',
            static function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->unsignedBigInteger('order_column')->index();
                $table->boolean('active')->default(true);
                $table->foreignId('make_id')
                    ->index()
                    ->constrained('tire_makes')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->foreignId('model_id')
                    ->index()
                    ->constrained('tire_models')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->foreignId('type_id')
                    ->index()
                    ->constrained('tire_types')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->foreignId('size_id')
                    ->index()
                    ->constrained('tire_sizes')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->float('ngp');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tire_specifications');
    }
};
