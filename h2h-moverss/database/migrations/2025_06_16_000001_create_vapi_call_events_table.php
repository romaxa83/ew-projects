<?php

use App\Models\Vapi\CallEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CallEvent::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('call_id');
            $table->string('type_event', 50);
            $table->string('type_call', 50);
            $table->string('reason_ended', 50);
            $table->string('caller_number', 50);
            $table->integer('duration');
            $table->string('recording_url')->nullable();
            $table->timestamp('call_start_at')->nullable();
            $table->timestamp('call_end_at')->nullable();

            $table->longText('misc')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CallEvent::TABLE);
    }
};
