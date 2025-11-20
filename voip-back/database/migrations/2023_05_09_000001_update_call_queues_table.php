<?php

use App\Models\Calls\Queue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Queue::TABLE,
            function (Blueprint $table) {
                $table->string('type', 10)->default(\App\Enums\Calls\QueueType::QUEUE());
            });
    }

    public function down(): void
    {
        Schema::table(Queue::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('type');
            });
    }
};


