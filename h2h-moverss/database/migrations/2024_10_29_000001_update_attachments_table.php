<?php

use App\Models\Attachment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table(Attachment::TABLE, function (Blueprint $table) {
            $table->string("entity_type")->nullable();
            $table->unsignedBigInteger("entity_id")->nullable();
            $table->index(['entity_type', 'entity_id'], 'idx_attachment_entity');
        });
    }

    public function down(): void
    {
        Schema::table(Attachment::TABLE, function (Blueprint $table) {
            $table->dropIndex('idx_attachment_entity');
            $table->dropColumn('entity_type');
            $table->dropColumn('entity_id');
        });
    }
};
