<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'stores',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sort')->default(0);
                $table->boolean('active')->default(true);
                $table->string('link');

                $table->foreignId('store_category_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
