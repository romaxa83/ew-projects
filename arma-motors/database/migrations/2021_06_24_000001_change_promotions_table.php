<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePromotionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_translations', function (Blueprint $table) {
            $table->longText('text')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('promotion_translations', function (Blueprint $table) {
            $table->string('text')->nullable()->change();
        });
    }
}
