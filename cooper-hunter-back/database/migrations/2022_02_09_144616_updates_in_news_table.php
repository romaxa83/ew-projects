<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'news',
            static function (Blueprint $table) {
                $table->foreignId('tag_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'news',
            static function (Blueprint $table) {
                $table->dropForeign(['tag_id']);
                $table->dropColumn('tag_id');
            }
        );
    }
};
