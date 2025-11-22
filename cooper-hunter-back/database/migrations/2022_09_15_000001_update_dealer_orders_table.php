<?php

use App\Models\Orders\Dealer\Order;
use App\Models\Payments\PaymentCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('payment_card_id')->nullable();
                $table->foreign('payment_card_id')
                    ->references('id')
                    ->on(PaymentCard::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->dropForeign(['payment_card_id']);
            $table->dropColumn(['payment_card_id']);
        });
    }
};
