<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'product_keywords',
            static function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('keyword')->index();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_keywords');
    }
};
