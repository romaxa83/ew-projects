<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'system_unit',
            static function (Blueprint $table) {
                $table->dropUnique(['serial_number']);

                $table->unique(['system_id', 'serial_number']);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'system_unit',
            static function (Blueprint $table) {
                $table->dropForeign(['system_id']);

                $table->dropUnique(['system_id', 'serial_number']);

                $table->unique(['serial_number']);

                $table->foreign('system_id')
                    ->references('id')
                    ->on('systems')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }
};
