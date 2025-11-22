<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'find_solution_statistics',
            static function (Blueprint $table) {
                $table->id();

                $table->string('outdoor');
                $table->string('outdoor_btu');
                $table->string('outdoor_voltage');
                $table->string('climate_zone');
                $table->string('series');

                $table->json('indoors');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('find_solution_statistics');
    }
};
