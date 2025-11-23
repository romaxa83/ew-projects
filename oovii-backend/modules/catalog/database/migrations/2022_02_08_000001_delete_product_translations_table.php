<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\ProductTranslation;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('product_translations');
    }

    public function down(): void
    {
        Schema::table(ProductTranslation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('locale')->index();
                $table->string('name');
                $table->string('slug');
                $table->mediumText('text')->nullable();
                $table->seo();

                $table->unique(['product_id', 'locale']);
                $table->unique(['slug', 'locale']);
            }
        );
    }
};

