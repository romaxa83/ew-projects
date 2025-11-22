<?php

use App\Models\Orders\Parts\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Item::TABLE, function (Blueprint $table) {
            $table->decimal('discount', 10, 2)
                ->default(0);
            $table->dropColumn('is_updated_price');
        });
    }

    public function down(): void
    {
        Schema::table(Item::TABLE, function (Blueprint $table) {
            $table->dropColumn('discount');
            $table->boolean('is_updated_price')
                ->default(false);
        });
    }
};
