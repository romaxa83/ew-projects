<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'warranty_registration_units_pivot',
            static function (Blueprint $table) {
                $table->dropForeign(['system_id']);
            }
        );

        Schema::table(
            'warranty_registration_units_pivot',
            static function (Blueprint $table) {
                $table->dropColumn('system_id');

                $table->unsignedBigInteger('warranty_registration_id')->first();

                $table->foreign('warranty_registration_id', 'warranty_registration_id_foreign')
                    ->on('warranty_registrations')
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'warranty_registration_units_pivot',
            static function (Blueprint $table) {
                $table->foreignId('system_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->dropForeign('warranty_registration_id_foreign');
            }
        );

        Schema::table(
            'warranty_registration_units_pivot',
            static function (Blueprint $table) {
                $table->dropColumn('warranty_registration_id');
            }
        );
    }
};
