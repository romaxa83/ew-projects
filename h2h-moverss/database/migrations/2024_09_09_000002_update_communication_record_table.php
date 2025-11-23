<?php

use App\Models\Communications\CommunicationRecord;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommunicationRecord::TABLE, function (Blueprint $table) {
            $table->integer('order_id')
                ->nullable()
                ->references('id')
                ->on(Order::TABLE)
                ->onDelete('cascade')
            ;
        });
    }

    public function down(): void
    {
        Schema::table(CommunicationRecord::TABLE, function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};
