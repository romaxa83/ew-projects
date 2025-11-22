<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'our_case_categories',
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('sort')->default(0);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('our_case_categories');
    }
};
