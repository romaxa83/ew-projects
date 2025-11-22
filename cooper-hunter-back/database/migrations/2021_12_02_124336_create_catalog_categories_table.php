<?php

use App\Models\Catalog\Categories\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'catalog_categories',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(Category::DEFAULT_SORT);
                $table->boolean('active')->default(Category::DEFAULT_ACTIVE);

                $table->unsignedBigInteger('parent_id')->nullable();
                $table->foreign('parent_id')
                    ->on('catalog_categories')
                    ->references('id')
                    ->onDelete('cascade');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_categories');
    }
};
