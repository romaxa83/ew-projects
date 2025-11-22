<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'store_categories',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sort')->default(0);
                $table->boolean('active')->default(true);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('store_categories');
    }
};
