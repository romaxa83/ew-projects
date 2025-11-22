<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'users',
            static function (Blueprint $table) {
                $table->dropColumn('middle_name');
            }
        );

        Schema::table(
            'technicians',
            static function (Blueprint $table) {
                $table->dropColumn('middle_name');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'users',
            static function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('last_name');
            }
        );

        Schema::table(
            'technicians',
            static function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('last_name');
            }
        );
    }
};
