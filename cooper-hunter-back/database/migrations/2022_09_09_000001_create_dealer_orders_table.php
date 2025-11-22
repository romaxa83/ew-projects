<?php

use App\Enums\Orders\Dealer as DealerEnum;
use App\Models\Companies\ShippingAddress;
use App\Models\Orders\Dealer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Dealer\Order::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->uuid('guid')->nullable()->unique();

                $table->unsignedBigInteger('dealer_id');
                $table->foreign('dealer_id')
                    ->references('id')
                    ->on(\App\Models\Dealers\Dealer::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedBigInteger('shipping_address_id')
                    ->nullable();
                $table->foreign('shipping_address_id')
                    ->references('id')
                    ->on(ShippingAddress::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('status', 10)
                    ->default(DealerEnum\OrderStatus::DRAFT());
                $table->string('delivery_type', 30)
                    ->default(DealerEnum\DeliveryType::NONE());
                $table->string('payment_type', 30)
                    ->default(DealerEnum\PaymentType::NONE());

                $table->string('po', 50)->nullable();

                $table->string('tracking_number', 50)->nullable();
                $table->string('tracking_company', 150)->nullable();
                $table->string('terms')->nullable();
                $table->mediumText('comment')->nullable();

                $table->timestamp('shipped_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Dealer\Order::TABLE);
    }
};
