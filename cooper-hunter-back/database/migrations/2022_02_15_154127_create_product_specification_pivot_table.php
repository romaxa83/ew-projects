<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'product_specification',
            static function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->constrained('catalog_products')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('specification_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specification');
    }
};
