<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'tires',
            static function (Blueprint $table) {
                $table->foreignId('changes_reason_id')
                    ->nullable()
                    ->constrained('tire_changes_reasons')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->text('changes_reason_description')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'tires',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('changes_reason_id');
                $table->dropColumn('changes_reason_description');
            }
        );
    }
};
