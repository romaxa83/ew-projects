<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'vehicle_class_vehicle_type',
            static function (Blueprint $table) {
                $table->id();

                $table->foreignId('vehicle_class_id')
                    ->constrained('vehicle_classes')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreignId('vehicle_type_id')
                    ->constrained('vehicle_types')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_class_vehicle_type');
    }
};
