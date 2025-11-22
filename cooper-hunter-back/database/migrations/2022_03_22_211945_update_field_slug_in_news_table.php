<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'news',
            static function (Blueprint $table) {
                $table->dropColumn('slug');
            }
        );

        Schema::table(
            'news',
            static function (Blueprint $table) {
                $table->string('slug')->after('id');
            }
        );

        Schema::table(
            'news',
            static function (Blueprint $table) {
                DB::statement('update news set news.slug = concat(\'slug-value-\', news.id) where true;');
                $table->unique('slug');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'news',
            static function (Blueprint $table) {
            }
        );
    }
};
