<?php

use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'solutions',
            static function (Blueprint $table)
            {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained(Product::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->enum(
                    'type',
                    SolutionTypeEnum::getValues()
                );

                $table->enum(
                    'indoor_type',
                    SolutionIndoorEnum::getValues()
                )
                    ->nullable();

                $table->unsignedInteger('btu')
                    ->nullable();

                $table->unsignedInteger('voltage')
                    ->nullable();

                $table->enum(
                    'zone',
                    SolutionZoneEnum::getValues()
                )
                    ->nullable();

                $table->foreignId('series_id')
                    ->nullable()
                    ->constrained(SolutionSeries::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('solutions');
    }
};
