<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'vehicles',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('vin');
                $table->foreignId('client_id')
                    ->constrained('clients')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
