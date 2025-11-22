<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'technicians',
            static function (Blueprint $table) {
                $table->unique('guid', 'technicians_guid_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'technicians',
            static function (Blueprint $table) {
                $table->dropIndex('technicians_guid_unique');
            }
        );
    }
};
