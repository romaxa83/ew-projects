<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'schema_axles',
            static function (Blueprint $table)
            {
                $table->id();
                $table->unsignedInteger('position');
                $table->string('name');
                $table->foreignId('schema_vehicle_id')
                    ->constrained('schema_vehicles')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_axles');
    }
};
