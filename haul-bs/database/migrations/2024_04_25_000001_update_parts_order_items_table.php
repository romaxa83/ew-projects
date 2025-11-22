<?php

use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Parts\Item::TABLE, function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_old', 10, 2)->nullable();
            $table->decimal('delivery_cost', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Parts\Item::TABLE, function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('price_old');
            $table->dropColumn('delivery_cost');
        });
    }
};
