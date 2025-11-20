<?php

use App\Models\Localization\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('article_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->longText('text');
                $table->string('description')->nullable();

                $table->unsignedInteger('row_id');
                $table->foreign('row_id')
                    ->on('articles')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('language')->nullable();
                $table->foreign('language')
                    ->on(Language::TABLE)
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('article_translations');
    }
};

