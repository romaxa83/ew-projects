<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'certificate_product',
            static function (Blueprint $table) {
                $table->foreignId('certificate_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->constrained('catalog_products')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_product');
    }
};
