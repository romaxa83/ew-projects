<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\ProductTranslation;


return new class extends Migration {
    public function up(): void
    {
        Schema::create(ProductTranslation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('locale')->index();
                $table->string('name');
                $table->mediumText('description')->nullable();
                $table->string('feature_1',1000)->nullable();
                $table->string('feature_2',1000)->nullable();
                $table->string('feature_3',1000)->nullable();

                $table->unique(['product_id', 'locale']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};
