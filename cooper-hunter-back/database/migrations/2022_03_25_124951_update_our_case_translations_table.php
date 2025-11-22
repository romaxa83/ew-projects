<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'our_case_translations',
            static function (Blueprint $table)
            {
                $table->dropColumn('seo_title');
                $table->dropColumn('seo_description');
                $table->dropColumn('seo_h1');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'our_case_translations',
            static function (Blueprint $table)
            {
                $table->string('seo_title')
                    ->nullable();
                $table->string('seo_description')
                    ->nullable();
                $table->string('seo_h1')
                    ->nullable();
            }
        );
    }
};
