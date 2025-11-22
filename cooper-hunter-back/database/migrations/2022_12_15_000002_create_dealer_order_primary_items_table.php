<?php

use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\PrimaryItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(PrimaryItem::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('item_id');
                $table->foreign('item_id')
                    ->references('id')
                    ->on(Item::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('qty');
                $table->unsignedInteger('price');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(PrimaryItem::TABLE);
    }
};
