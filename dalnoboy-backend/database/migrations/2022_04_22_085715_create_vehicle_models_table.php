<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'vehicle_models',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_column')->index();
                $table->timestamps();
                $table->foreignId('vehicle_make_id')
                    ->constrained('vehicle_makes')
                    ->references('id')
                    ->cascadeOnUpdate();
                $table->boolean('active')->default(true);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
