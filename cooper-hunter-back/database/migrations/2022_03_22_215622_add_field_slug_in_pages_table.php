<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'pages',
            static function (Blueprint $table) {
                $table->string('slug')->after('id');
            }
        );

        Schema::table(
            'pages',
            static function (Blueprint $table) {
                DB::statement('update pages set pages.slug = concat(\'slug-value-\', pages.id) where true;');
                $table->unique('slug');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'pages',
            static function (Blueprint $table) {
                $table->dropColumn('slug');
            }
        );
    }
};
