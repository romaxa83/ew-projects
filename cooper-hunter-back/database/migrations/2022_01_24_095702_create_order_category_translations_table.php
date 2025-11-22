<?php

use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Categories\OrderCategoryTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            OrderCategoryTranslation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->string('slug')->index();
                $table->string('title');
                $table->mediumText('description')->nullable();

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->on(OrderCategory::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('language')->nullable();
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OrderCategoryTranslation::TABLE);
    }
};
