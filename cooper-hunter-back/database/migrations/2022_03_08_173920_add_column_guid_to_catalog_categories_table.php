<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_categories',
            static function (Blueprint $table) {
                $table->string('guid', 36)->after('id')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_categories',
            static function (Blueprint $table) {
                $table->dropColumn('guid');
            }
        );
    }
};
