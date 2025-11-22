<?php

use App\Models\Saas\Company\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->tinyInteger('after_trial_count')->default(0);
            $table->boolean('send_before_trial')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('after_trial_count');
            $table->dropColumn('send_before_trial');
        });
    }
};
