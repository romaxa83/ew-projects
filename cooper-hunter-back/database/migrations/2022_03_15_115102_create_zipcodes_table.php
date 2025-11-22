<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'zipcodes',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedInteger('state_id');
                $table->foreign('state_id')
                    ->on('states')
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('zip')->unique();
                $table->point('coordinates')->spatialIndex();
                $table->string('name');
                $table->string('timezone');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('zipcodes');
    }
};
