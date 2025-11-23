<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\CollectionTranslation;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CollectionTranslation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_id')
                    ->constrained()->cascadeOnDelete();
                $table->string('locale')->index();
                $table->string('name');
                $table->string('image')->nullable();

                $table->unique(['collection_id', 'locale']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(CollectionTranslation::TABLE);
    }
};
