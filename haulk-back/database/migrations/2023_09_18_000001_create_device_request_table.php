<?php

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(DeviceRequest::TABLE_NAME, function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->references('id')
                ->on(Company::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->onUpdate('cascade');

            $table->string('status', 15)->default(DeviceRequestStatus::NEW());

            $table->integer('qty')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(DeviceRequest::TABLE_NAME);
    }
};

