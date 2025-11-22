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
                $table->unsignedBigInteger('system_id')
                    ->nullable()
                    ->change();

                $table->dropForeign(['system_id']);
                $table->foreign('system_id')
                    ->references('id')
                    ->on('systems')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
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
                $table->unsignedBigInteger('system_id')->nullable(false)->change();
                $table->foreign('system_id')
                    ->references('id')
                    ->on('systems')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }
};
