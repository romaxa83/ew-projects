<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'schema_wheels',
            static function (Blueprint $table)
            {
                $table->id();
                $table->unsignedInteger('position');
                $table->string('name');
                $table->integer('pos_x');
                $table->integer('pos_y');
                $table->integer('rotate');
                $table->foreignId('schema_axle_id')
                    ->constrained('schema_axles')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->boolean('use')
                    ->default(true);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_wheels');
    }
};
