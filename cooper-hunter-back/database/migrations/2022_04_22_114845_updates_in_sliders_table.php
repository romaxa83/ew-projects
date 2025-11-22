<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('sliders', 'link')) {
            Schema::table(
                'sliders',
                static function (Blueprint $table) {
                    $table->string('link')->nullable();
                }
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sliders', 'link')) {
            Schema::table(
                'sliders',
                static function (Blueprint $table) {
                    $table->dropColumn('link');
                }
            );
        }
    }
};
