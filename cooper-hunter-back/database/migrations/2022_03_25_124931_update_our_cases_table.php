<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'our_cases',
            static function (Blueprint $table)
            {
                $table->dropColumn('slug');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'our_cases',
            static function (Blueprint $table)
            {
                $table->string('slug')
                    ->after('id');
            }
        );

        Schema::table(
            'our_cases',
            static function (Blueprint $table)
            {
                DB::statement(
                    'update our_cases set our_cases.slug = concat(\'slug-value-\', our_cases.id) where true;'
                );
                $table->unique('slug');
            }
        );
    }
};
