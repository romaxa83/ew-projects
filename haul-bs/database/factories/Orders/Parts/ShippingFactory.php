<?php

namespace Database\Factories\Orders\Parts;

use App\Enums\Orders\Parts\ShippingMethod;
use App\Models\Orders\Parts\Shipping;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\Parts\Shipping>
 */
class ShippingFactory extends BaseFactory
{
    protected $model = Shipping::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'method' => ShippingMethod::Pickup(),
            'cost' => 0,
        ];
    }
}
