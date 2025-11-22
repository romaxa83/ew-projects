<?php

use App\Models\Saas\Company\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('gps_enabled_start_at')->nullable();
            $table->timestamp('gps_enabled_end_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('gps_enabled_start_at');
            $table->dropColumn('gps_enabled_end_at');
        });
    }
};
