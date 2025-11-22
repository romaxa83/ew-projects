<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_features',
            static function (Blueprint $table) {
                $table->boolean('display_in_mobile')->default(false)->after('active');
                $table->boolean('display_in_web')->default(false)->after('active');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_features',
            static function (Blueprint $table) {
                $table->dropColumn('display_in_mobile', 'display_in_web');
            }
        );
    }
};
