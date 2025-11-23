<?php

namespace Database\Factories\Orders;

use App\Models\Material;
use App\Models\Order;
use Database\Factories\BaseFactory;

class MaterialFactory extends BaseFactory
{
    protected $model = Order\Material::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type_id' => 1,
            'order_id' => Order::factory(),
            'material_id' => Material::factory(),
            'title' => $this->faker->title(),
            'price' => 12,
            'qty' => 2,
            'need_packing' => 1,
            'need_unpacking' => 0,
            'packing_price' => 1.7,
            'unpacking_price' => 1,
        ];
    }
}
