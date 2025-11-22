<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Notifications\Notification;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Notification::TABLE_NAME, function (Blueprint $table) {
            $table->json('meta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Notification::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};



