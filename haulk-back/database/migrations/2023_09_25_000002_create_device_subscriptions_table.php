<?php

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new
class extends Migration {
    public function up(): void
    {
        Schema::create(DeviceSubscription::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('status', 15)
                ->default(DeviceSubscriptionStatus::ACTIVE());
            $table->foreignId('company_id')
                ->references('id')
                ->on(Company::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamp('activate_at')->nullable();
            $table->timestamp('activate_till_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(DeviceSubscription::TABLE_NAME);
    }
};
