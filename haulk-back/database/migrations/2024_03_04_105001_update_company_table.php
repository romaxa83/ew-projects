<?php

use App\Models\Saas\Company\Company;
use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->string('ga_id', 512)->nullable();
            $table->timestamp('registration_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn(['ga_id', 'registration_at']);
        });
    }
};
