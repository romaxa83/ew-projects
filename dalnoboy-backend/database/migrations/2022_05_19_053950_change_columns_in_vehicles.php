<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table) {
                $table->renameColumn('was_moderated', 'is_moderated');
                $table->dropUnique('vin');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table) {
                $table->renameColumn('is_moderated', 'was_moderated');
                $table->unique('vin');
            }
        );
    }
};
