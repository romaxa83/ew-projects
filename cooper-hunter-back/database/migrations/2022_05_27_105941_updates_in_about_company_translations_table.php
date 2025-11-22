<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'about_company_translations',
            static function (Blueprint $table) {
                $table->string('additional_title')->nullable();
                $table->text('additional_description')->nullable();
                $table->string('additional_video_link')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'about_company_translations',
            static function (Blueprint $table) {
                $table->dropColumn('additional_title');
                $table->dropColumn('additional_description');
                $table->dropColumn('additional_video_link');
            }
        );
    }
};
