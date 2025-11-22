<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'tire_diameters',
            static function (Blueprint $table) {
                $table->id();
                $table->string('value', 20);
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('order_column')->index();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tire_diameters');
    }
};
