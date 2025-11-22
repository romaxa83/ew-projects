<?php

use App\Models\Inventories\Features\Feature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Feature::TABLE, function (Blueprint $table) {
            $table->id();

            $table->boolean('active')->default(true);
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('multiple')->default(false);
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Feature::TABLE);
    }
};
