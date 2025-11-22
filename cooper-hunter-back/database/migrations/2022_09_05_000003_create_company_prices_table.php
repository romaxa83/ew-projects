<?php

use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Companies\Price;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Price::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('company_id');
                $table->foreign('company_id')
                    ->references('id')
                    ->on(Company::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('price')->default(0);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Price::TABLE);
    }
};

