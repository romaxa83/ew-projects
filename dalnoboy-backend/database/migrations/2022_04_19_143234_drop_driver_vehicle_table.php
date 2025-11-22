<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('driver_vehicle');
    }

    public function down(): void
    {
        Schema::create(
            'driver_vehicle',
            static function (Blueprint $table)
            {
                $table->foreignId('driver_id')
                    ->constrained('drivers')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('vehicle_id')
                    ->constrained('vehicles')
                    ->references('id')
                    ->cascadeOnDelete();
            }
        );
    }
};
