<?php

use App\Models\Client;
use App\Models\Vapi\CallEvent;
use App\Models\Vapi\ClientRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(ClientRequest::TABLE, function (Blueprint $table) {
            $table->id();

            $table->integer('call_rec_id')
                ->nullable()
                ->references('id')
                ->on(CallEvent::TABLE);
            $table->integer('client_id')
                ->nullable()
                ->references('id')
                ->on(Client::TABLE);

            $table->string('caller_number', 50);
            $table->string('client_name')->nullable();
            $table->string('client_number')->nullable();
            $table->string('department_type', 50);
            $table->timestamp('call_back_at')->nullable();

            $table->longText('misc')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(ClientRequest::TABLE);
    }
};
