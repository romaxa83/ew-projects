<?php

use App\Enums\Orders\Parts\OrderSource;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->string('source', 30)->default(OrderSource::BS);
        });
    }

    public function down(): void
    {
        Schema::table(Parts\Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
