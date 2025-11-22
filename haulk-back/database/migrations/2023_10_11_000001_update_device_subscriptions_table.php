<?php

use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(DeviceSubscription::TABLE_NAME, function (Blueprint $table) {
            $table->decimal('current_rate', 10, 2)
                ->default(config('billing.gps.price'));
            $table->decimal('next_rate', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(DeviceSubscription::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('current_rate');
            $table->dropColumn('next_rate');
        });
    }
};
