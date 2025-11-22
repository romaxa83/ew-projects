<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'specifications',
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('sort')->default(0);
                $table->string('icon');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
