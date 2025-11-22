<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'manuals',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('manual_group_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('manuals');
    }
};
