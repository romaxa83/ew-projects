<?php

namespace Database\Factories\Orders\Dealer;

use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;
use App\Models\Companies\ShippingAddress;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Order[]|Order create(array $attributes = [])
 */
class OrderFactory extends BaseFactory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'dealer_id' => Dealer::factory(),
            'shipping_address_id' => ShippingAddress::factory(),
            'status' => OrderStatus::DRAFT(),
            'type' => OrderType::ORDINARY(),
            'delivery_type' => DeliveryType::NONE(),
            'payment_type' => PaymentType::NONE(),
            'po' => $this->faker->creditCardNumber,
            'terms' => null,
            'comment' => $this->faker->sentence,
            'payment_card_id' => null,
            'files' => null,
            'tax' => 0,
            'shipping_price' => 0,
            'total' => 0,
            'total_discount' => 0,
            'total_with_discount' => 0,
            'invoice' => null,
            'invoice_at' => null,
            'has_invoice' => false,
            'approved_at' => null,
            'hash' => null,
        ];
    }
}
