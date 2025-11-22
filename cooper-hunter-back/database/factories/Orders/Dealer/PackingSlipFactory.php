<?php

namespace Database\Factories\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|PackingSlip[]|PackingSlip create(array $attributes = [])
 */
class PackingSlipFactory extends BaseFactory
{
    protected $model = PackingSlip::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'status' => OrderStatus::DRAFT(),
            'order_id' => Order::factory(),
            'number' => $this->faker->postcode,
            'tracking_number' => $this->faker->creditCardNumber,
            'tracking_company' => $this->faker->company,
            'tax' => $this->faker->randomFloat(2, 10,15),
            'shipping_price' => $this->faker->randomFloat(2, 500,700),
            'total' => $this->faker->randomFloat(2, 500,700),
            'total_discount' => $this->faker->randomFloat(2, 500,700),
            'total_with_discount' => $this->faker->randomFloat(2, 500,700),
            'invoice' => $this->faker->creditCardNumber,
            'shipped_at' => CarbonImmutable::now(),
            'invoice_at' => CarbonImmutable::now(),
            'files' => null,
        ];
    }
}
