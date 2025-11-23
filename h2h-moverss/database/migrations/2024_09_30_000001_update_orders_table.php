<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->unsignedInteger('base_id')
                ->nullable()
                ->references('id')
                ->on(Order::TABLE)
                ->onDelete('cascade')
            ;
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('base_id');
        });
    }
};
