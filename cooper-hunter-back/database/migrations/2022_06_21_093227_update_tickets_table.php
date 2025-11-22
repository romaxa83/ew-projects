<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'tickets',
            static function (Blueprint $table) {
                $table->dropIndex('ticket_code_index');

                $table->unique('code', 'ticket_code_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'tickets',
            static function (Blueprint $table) {
                $table->dropUnique('ticket_code_unique');

                $table->index('code', 'ticket_code_index');
            }
        );
    }
};
