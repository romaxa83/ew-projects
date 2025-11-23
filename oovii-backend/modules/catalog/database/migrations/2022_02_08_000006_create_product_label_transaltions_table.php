<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Labels\LabelTranslation;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(LabelTranslation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('label_id')
                    ->constrained()->cascadeOnDelete();
                $table->string('locale')->index();
                $table->string('name');

                $table->unique(['label_id', 'locale']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(LabelTranslation::TABLE);
    }
};
