<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'global_settings',
            static function (Blueprint $table) {
                $table->unsignedTinyInteger('slider_countdown')->default(5);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'global_settings',
            static function (Blueprint $table) {
                $table->dropColumn('slider_countdown');
            }
        );
    }
};
