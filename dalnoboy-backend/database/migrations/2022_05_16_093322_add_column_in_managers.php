<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'managers',
            static function (Blueprint $table) {
                $table
                    ->foreignId('region_id')
                    ->nullable()
                    ->after('second_name')
                    ->constrained('regions')
                    ->references('id')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'managers',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('region_id');
            }
        );
    }
};
