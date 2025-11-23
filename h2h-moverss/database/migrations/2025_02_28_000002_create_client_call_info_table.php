<?php

use App\Models\Client;
use App\Models\Communications\CallInfo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CallInfo::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string("channel_contact");

            $table->integer('client_id')
                ->nullable()
                ->references('id')
                ->on(Client::TABLE)
                ->onDelete('cascade');

            $table->decimal("score", 10, 2);
            $table->text("details")->nullable();
            $table->string("call_id");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CallInfo::TABLE);
    }
};
