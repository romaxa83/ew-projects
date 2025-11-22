<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'chat_menus',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('sort')->index()->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'chat_menus',
            static function (Blueprint $table) {
                $table->dropIndex(['sort']);
                $table->dropColumn('sort');
            }
        );
    }
};
