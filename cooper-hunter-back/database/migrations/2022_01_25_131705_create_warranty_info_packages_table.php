<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'warranty_info_packages',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('warranty_info_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedInteger('sort');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_info_packages');
    }
};
