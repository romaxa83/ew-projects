<?php

use App\Models\Billing\Invoice;
use App\Models\Saas\Company\Company;
use App\Models\Saas\Pricing\CompanySubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CompanySubscription::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('set_regular_plan_at');
        });
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('after_trial_count');
        });
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->tinyInteger('count_send_not_paid')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table(CompanySubscription::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('set_regular_plan_at')->nullable();
        });
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->tinyInteger('after_trial_count')->default(0);
        });
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('count_send_not_paid');
        });
    }
};
