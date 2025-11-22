<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'certificates',
            static function (Blueprint $table) {
                $table->unique(['certificate_type_id', 'number', 'link'], 'cert_link_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'certificates',
            static function (Blueprint $table) {
                $table->dropForeign(['certificate_type_id']);
            }
        );

        Schema::table(
            'certificates',
            static function (Blueprint $table) {
                $table->dropUnique('cert_link_unique');
            }
        );

        Schema::table(
            'certificates',
            static function (Blueprint $table) {
                $table->foreign('certificate_type_id')
                    ->on('certificate_types')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }
};
