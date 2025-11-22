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
            $table->boolean('is_updated_price')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Item::TABLE, function (Blueprint $table) {
            $table->dropColumn('is_updated_price');
        });
    }
};
