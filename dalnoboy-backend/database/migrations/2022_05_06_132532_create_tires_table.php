<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'tires',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_column')->index();
                $table->timestamps();
                $table->boolean('active')->default(true);
                $table->string('serial_number');
                $table->foreignId('specification_id')
                    ->index()
                    ->constrained('tire_specifications')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('relationship_type_id')
                    ->index()
                    ->constrained('tire_relationship_types')
                    ->references('id')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tires');
    }
};
