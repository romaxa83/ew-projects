<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Labels\Label;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Label::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('published')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Label::TABLE);
    }
};
