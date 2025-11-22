<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'chat_messages',
            static function (Blueprint $table) {
                $table->boolean('mark_seen')
                    ->after('participation_id')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'chat_messages',
            static function (Blueprint $table) {
                $table->dropColumn('mark_seen');
            }
        );
    }
};
