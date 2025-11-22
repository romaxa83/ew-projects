<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_products',
            static function (Blueprint $table) {
                $table->unsignedFloat('seer')->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_products',
            static function (Blueprint $table) {
                $table->unsignedInteger('seer')->change();
            }
        );
    }
};
