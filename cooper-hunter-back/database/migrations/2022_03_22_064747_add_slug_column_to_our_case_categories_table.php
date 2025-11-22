<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'our_case_categories',
            static function (Blueprint $table) {
                $table->string('slug')->after('id');
            }
        );

        Schema::table(
            'our_case_categories',
            static function (Blueprint $table) {
                DB::statement('update our_case_categories set slug = concat(\'slug-value-\', our_case_categories.id) where true;');
                $table->unique('slug');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'our_case_categories',
            static function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        );
    }
};
