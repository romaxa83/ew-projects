<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'schema_vehicles',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('name');
                $table->boolean('is_default')
                    ->default(false);
                $table->string('vehicle_form')
                    ->comment('See all types in enum VehicleFormEnum');
                $table->timestamps();

                $table->unique(['name', 'is_default', 'vehicle_form']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_vehicles');
    }
};
