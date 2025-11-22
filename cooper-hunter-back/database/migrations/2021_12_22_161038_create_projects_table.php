<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'projects',
            static function (Blueprint $table) {
                $table->id();
                $table->numericMorphs('member');
                $table->string('name');

                $table->nullableTimestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
