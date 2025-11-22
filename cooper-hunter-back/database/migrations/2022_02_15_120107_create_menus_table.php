<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'menus',
            static function (Blueprint $table) {
                $table->id();

                $table->foreignId('parent_id')
                    ->nullable()
                    ->constrained('menus')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->string('position');
                $table->boolean('active');
                $table->unsignedBigInteger('sort')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
