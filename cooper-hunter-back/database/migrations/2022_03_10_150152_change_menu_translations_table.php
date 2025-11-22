<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'menu_translations',
            static function (Blueprint $table)
            {
                $table->dropColumn('link');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'menu_translations',
            static function (Blueprint $table)
            {
                $table->string('link', 1000)
                    ->after('title');
            }
        );
    }
};
