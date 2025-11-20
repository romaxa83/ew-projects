<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkToPromotionsTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::table('promotions_translations', function (Blueprint $table) {
            $table->string('link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('promotions_translations', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
}
