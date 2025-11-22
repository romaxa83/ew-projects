<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'solutions',
            static function (Blueprint $table)
            {
                $table
                    ->string('short_name')
                    ->after('type')
                    ->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'solutions',
            static function (Blueprint $table)
            {
                $table->dropColumn('short_name');
            }
        );
    }
};
