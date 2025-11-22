<?php

use App\Models\Catalog\Brands\Brand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Brand::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Brand::TABLE);
    }
};
