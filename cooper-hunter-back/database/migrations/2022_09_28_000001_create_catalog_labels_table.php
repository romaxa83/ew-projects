<?php

use App\Models\Catalog\Labels\Label;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Label::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->string('color_type', 50);
                $table->boolean('active')->default(true);
                $table->unsignedInteger('sort')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Label::TABLE);
    }
};
