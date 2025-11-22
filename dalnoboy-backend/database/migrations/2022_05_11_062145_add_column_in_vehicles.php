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
                $table
                    ->boolean('was_moderated')
                    ->after('vin')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table) {
                $table->dropColumn('was_moderated');
            }
        );
    }
};
