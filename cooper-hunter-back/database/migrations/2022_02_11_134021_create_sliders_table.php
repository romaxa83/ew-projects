<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'sliders',
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('sort')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
