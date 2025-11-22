<?php

use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(DeviceSubscription::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('access_till_at')->nullable();
            $table->boolean('send_warning_notify')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(DeviceSubscription::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('access_till_at');
            $table->dropColumn('send_warning_notify');
        });
    }
};





