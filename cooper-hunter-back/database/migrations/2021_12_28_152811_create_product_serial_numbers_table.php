<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'product_serial_numbers',
            static function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->constrained(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('serial_number')->unique()->index();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serial_numbers');
    }
};
