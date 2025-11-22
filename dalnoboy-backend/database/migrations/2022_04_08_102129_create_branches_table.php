<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'branches',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('name');
                $table->string('city');
                $table->foreignId('region_id')
                    ->constrained('regions')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->text('address');
                $table->unsignedInteger('total_employers');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
