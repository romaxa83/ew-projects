<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Category;
use WezomCms\Catalog\Models\Collections\CategoryTranslation;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CategoryTranslation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->foreign('category_id')
                    ->references('id')
                    ->on(Category::TABLE)
                    ->onDelete('cascade');
                $table->string('locale')->index();
                $table->string('name');

                $table->unique(['category_id', 'locale'], "uniq_cct");
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(CategoryTranslation::TABLE);
    }
};
