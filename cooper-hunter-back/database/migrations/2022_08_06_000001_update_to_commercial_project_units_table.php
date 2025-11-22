<?php

use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialProjectUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommercialProjectUnit::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')
                    ->after('commercial_project_id')->nullable();
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->dropColumn('name');
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialProjectUnit::TABLE, function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->string('name')->nullable();
        });
    }
};





