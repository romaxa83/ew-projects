<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'support_request_messages',
            static function (Blueprint $table)
            {
                $table->boolean('is_read')
                    ->after('sender_id')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'support_request_messages',
            static function (Blueprint $table)
            {
                $table->dropColumn('is_read');
            }
        );
    }
};
