<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table)
            {
                $table->dropConstrainedForeignId('trailer_id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table)
            {
                $table->foreignId('trailer_id')
                    ->after('odo')
                    ->nullable()
                    ->constrained('vehicles')
                    ->references('id')
                    ->nullOnDelete();
            }
        );
    }
};
