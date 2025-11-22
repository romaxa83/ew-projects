<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'inspections',
            static function (Blueprint $table)
            {
                $table->id();
                $table->foreignId('inspector_id')
                    ->nullable()
                    ->constrained('users')
                    ->references('id')
                    ->nullOnDelete();
                $table->foreignId('vehicle_id')
                    ->constrained('vehicles')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('driver_id')
                    ->constrained('drivers')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('odo')
                    ->nullable();
                $table->boolean('is_moderated')
                    ->default(false);
                $table->foreignId('inspection_reason_id')
                    ->nullable()
                    ->constrained('inspection_reasons')
                    ->references('id')
                    ->nullOnDelete();
                $table->text('inspection_reason_description')
                    ->nullable();
                $table->boolean('unable_to_sign')
                    ->default(false);

                $table->jsonb('moderation_fields')
                    ->nullable();

                $table
                    ->foreignId('main_id')
                    ->nullable()
                    ->constrained('inspections')
                    ->references('id');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
