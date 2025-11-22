<?php

use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('past_due_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('delivered_at');
            $table->dropColumn('past_due_at');
        });
    }
};
