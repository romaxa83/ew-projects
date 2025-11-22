<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'tire_changes_reasons',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('uuid')->unique();
                $table->unsignedBigInteger('order_column')->index();
                $table->boolean('need_description')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tire_changes_reasons');
    }
};
