<?php

use App\Models\Locations\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'regions',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('slug')
                    ->unique();
                $table->unsignedBigInteger('sort')
                    ->default(Region::DEFAULT_SORT);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
