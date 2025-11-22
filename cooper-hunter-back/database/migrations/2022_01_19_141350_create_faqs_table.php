<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'faqs',
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
