<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'phones',
            static function (Blueprint $table)
            {
                $table->dropUnique(['owner_type', 'phone']);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'phones',
            static function (Blueprint $table)
            {
                $table->unique(['owner_type', 'phone']);
            }
        );
    }
};
