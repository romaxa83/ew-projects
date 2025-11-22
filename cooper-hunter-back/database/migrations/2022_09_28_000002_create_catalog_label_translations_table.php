<?php

use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Labels\LabelTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(LabelTranslation::TABLE,
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('title');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->on(Label::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(LabelTranslation::TABLE);
    }
};
