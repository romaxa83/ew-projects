<?php

namespace Database\Factories\Orders;

use App\Models\Orders\OrderPayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|OrderPayment[]|OrderPayment create(array $attributes = [])
 */
class OrderPaymentFactory extends Factory
{
    protected $model = OrderPayment::class;

    public function definition(): array
    {
        $orderPrice = $this->faker->numberBetween(500, 5000);

        $discount = $this->faker->numberBetween(0, $orderPrice);

        return [
            'order_price_with_discount' => $orderPrice - $discount,
            'order_price' => $orderPrice,
            'shipping_cost' => $this->faker->numberBetween(500, 2500),
            'tax' => $this->faker->numberBetween(500, 2500),
            'discount' => $discount
        ];
    }
}
