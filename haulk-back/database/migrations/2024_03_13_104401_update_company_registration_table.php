<?php

use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CompanyRegistration::TABLE, function (Blueprint $table) {
            $table->tinyInteger('not_confirmed_send_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table(CompanyRegistration::TABLE, function (Blueprint $table) {
            $table->dropColumn('not_confirmed_send_count');
        });
    }
};
