<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->string('title')->unique();

                $table->dropUnique(['guid']);
                $table->dropColumn('guid');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table) {
                $table->dropUnique(['title']);
                $table->dropColumn('title');

                $table->string('guid', 36)->unique()->after('id')->nullable();
            }
        );
    }
};
