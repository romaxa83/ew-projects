<?php

use App\Models\Commercial\Tax;
use App\Models\Locations\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Country::TABLE,
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('alias', 20)->unique();
                $table->boolean('active')->default(true);
                $table->boolean('default')->default(false);
                $table->unsignedInteger('sort')
                    ->default(Country::DEFAULT_SORT);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Country::TABLE);
    }
};
