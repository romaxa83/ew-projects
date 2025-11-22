<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->text('notice')->nullable()->after('warranty_status');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->dropColumn('notice');
            }
        );
    }
};
