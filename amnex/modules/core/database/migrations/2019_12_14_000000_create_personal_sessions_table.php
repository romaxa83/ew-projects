<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_sessions', function (Blueprint $table) {
            $table->id();
            $table->morphs('sessionable');
            $table->unsignedBigInteger('device_id')->nullable();

            $table->foreign('device_id')
                ->references('id')
                ->on('devices')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_sessions');
    }
};
