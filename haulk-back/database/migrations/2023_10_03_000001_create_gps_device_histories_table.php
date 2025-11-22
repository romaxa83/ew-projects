<?php

use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new
class extends Migration {
    public function up(): void
    {
        Schema::create(DeviceHistory::TABLE_NAME, function (Blueprint $table) {
            $table->id();

            $table->string('type', 25);

            $table->foreignId('device_id')
                ->references('id')
                ->on(Device::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->json('changed_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(DeviceHistory::TABLE_NAME);
    }
};

