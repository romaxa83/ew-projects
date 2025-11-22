<?php

namespace Database\Factories\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\SerialNumber;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|SerialNumber[]|SerialNumber create(array $attributes = [])
 */
class SerialNumberFactory extends BaseFactory
{
    protected $model = SerialNumber::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'serial_number' => $this->faker->creditCardNumber
        ];
    }
}
