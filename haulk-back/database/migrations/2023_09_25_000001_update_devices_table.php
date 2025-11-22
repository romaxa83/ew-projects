<?php

use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('active_till_at')->nullable();
            $table->foreignId('send_request_user_id')
                ->nullable()
                ->references('id')
                ->on(User::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table(Device::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('active_till_at');
            $table->dropColumn('send_request_user_id');
        });
    }
};



