<?php

use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationFavorites;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ConversationFavorites::TABLE, function (Blueprint $table) {
            $table->unsignedInteger('communication_rec_id')
                ->nullable()
                ->references('id')
                ->on(CommunicationRecord::TABLE)
                ->onDelete('cascade')
            ;
        });
    }

    public function down(): void
    {
        Schema::table(ConversationFavorites::TABLE, function (Blueprint $table) {
            $table->dropColumn('communication_rec_id')
            ;
        });
    }
};
