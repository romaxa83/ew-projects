<?php

use App\Models\Saas\Pricing\CompanySubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CompanySubscription::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('set_regular_plan_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(CompanySubscription::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('set_regular_plan_at');
        });
    }
};
