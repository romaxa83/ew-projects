<?php

use App\Models\Communications\CommunicationRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommunicationRecord::TABLE, function (Blueprint $table) {
            $table->index('order_id', 'comm_rec_index-order_id');
            $table->index('sort_at', 'comm_rec_index-sort_at');
            $table->index('channel_contact', 'comm_rec_index-channel_contact');
            $table->index('entity_type', 'comm_rec_index-entity_type');
        });
    }

    public function down(): void
    {
        Schema::table(CommunicationRecord::TABLE, function (Blueprint $table) {
            $table->dropIndex('comm_rec_index-order_id');
            $table->dropIndex('comm_rec_index-sort_at');
            $table->dropIndex('comm_rec_index-channel_contact');
            $table->dropIndex('comm_rec_index-entity_type');
        });
    }
};
