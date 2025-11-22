<?php

use App\Enums\Notifications\NotificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Notifications\Notification as NotificationInner;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(NotificationInner::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('status', 15)->default(NotificationStatus::NEW());
            $table->string('type', 15);
            $table->string('place', 15);
            $table->string('message_key');
            $table->json('message_attr');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(NotificationInner::TABLE_NAME);
    }
};
