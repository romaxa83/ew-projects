<?php

use App\Models\Locations\Country;
use App\Models\Locations\CountryTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CountryTranslation::TABLE,
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('name');

                $table->unsignedInteger('row_id');
                $table->foreign('row_id')
                    ->on(Country::TABLE)
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
        Schema::dropIfExists(CountryTranslation::TABLE);
    }
};

