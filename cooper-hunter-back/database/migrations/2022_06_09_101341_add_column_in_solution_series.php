<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'solution_series',
            static function (Blueprint $table)
            {
                $table
                    ->boolean('use_once')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'solution_series',
            static function (Blueprint $table)
            {
                $table->dropColumn('use_once');
            }
        );
    }
};
