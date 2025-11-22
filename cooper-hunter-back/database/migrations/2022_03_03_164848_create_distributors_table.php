<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'distributors',
            static function (Blueprint $table) {
                $table->id();

                $table->boolean('active');

                $table->point('coordinates')->spatialIndex();

                $table->string('address');
                $table->string('address_metaphone')->index();

                $table->string('link');
                $table->string('phone');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};
