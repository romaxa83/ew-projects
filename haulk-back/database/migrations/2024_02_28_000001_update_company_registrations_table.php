<?php

use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CompanyRegistration::TABLE, function (Blueprint $table) {
            $table->timestamp('confirmed_send_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(CompanyRegistration::TABLE, function (Blueprint $table) {
            $table->dropColumn('confirmed_send_at');
        });
    }
};
