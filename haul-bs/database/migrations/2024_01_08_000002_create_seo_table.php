<?php

use App\Foundations\Modules\Seo\Models\Seo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Seo::TABLE, function (Blueprint $table) {
            $table->id();
            $table->numericMorphs('model');

            $table->string('h1')->nullable();
            $table->string('title')->nullable();
            $table->text('keywords')->nullable();
            $table->text('desc')->nullable();
            $table->text('text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Seo::TABLE);
    }
};
