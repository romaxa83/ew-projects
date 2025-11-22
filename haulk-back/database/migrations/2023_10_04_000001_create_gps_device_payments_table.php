<?php

use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DevicePayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new
class extends Migration {
    public function up(): void
    {
        Schema::create(DevicePayment::TABLE_NAME, function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')
                ->references('id')
                ->on(Device::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('company_id')
                ->references('id')
                ->on(Company::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->boolean('deactivate')->default(false);
            $table->decimal('amount', 10, 2)->nullable();

            $table->timestamp('date');

            $table->unique(['company_id', 'device_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(DevicePayment::TABLE_NAME);
    }
};
