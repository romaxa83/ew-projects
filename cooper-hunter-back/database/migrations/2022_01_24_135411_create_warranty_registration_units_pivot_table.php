<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'warranty_registration_units_pivot',
            static function (Blueprint $table) {
                $table->foreignId('system_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreignId('product_id')
                    ->constrained(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('serial_number');

                $table->unique(['system_id', 'serial_number']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_registration_units_pivot');
    }
};
