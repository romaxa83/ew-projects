<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Category;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Category::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('published')->default(true);
                $table->integer('sort')->default(0)->index();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Category::TABLE);
    }
};

