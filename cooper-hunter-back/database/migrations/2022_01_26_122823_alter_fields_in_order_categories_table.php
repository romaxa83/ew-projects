<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'order_categories',
            static function (Blueprint $table) {
                $table->boolean('is_default')->default(false);
                $table->boolean('need_description')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'order_categories',
            static function (Blueprint $table) {
                $table->dropColumn('is_default');
                $table->dropColumn('need_description');
            }
        );
    }
};
