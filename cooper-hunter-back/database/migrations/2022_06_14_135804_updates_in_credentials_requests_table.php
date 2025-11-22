<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'credentials_requests',
            static function (Blueprint $table) {
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('end_date')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'credentials_requests',
            static function (Blueprint $table) {
                $table->dropColumn('processed_at');
                $table->dropColumn('end_date');
            }
        );
    }
};
