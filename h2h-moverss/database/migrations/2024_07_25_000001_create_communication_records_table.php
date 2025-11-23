<?php

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CommunicationRecord::TABLE, function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');

            $table->integer('client_id')
                ->nullable()
                ->references('id')
                ->on(Client::TABLE)
            ;
            $table->json('client_ids')->nullable();

            $table->integer('division_id')
                ->nullable()
                ->references('id')
                ->on(Division::TABLE)
                ->onDelete('cascade')
            ;

            $table->string('type', 30);

            $table->boolean('is_answered')->default(false);
            $table->string('channel_contact')->nullable();
            $table->timestamp('sort_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CommunicationRecord::TABLE);
    }
};
