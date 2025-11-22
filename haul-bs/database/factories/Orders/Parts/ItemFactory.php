<?php

namespace Database\Factories\Orders\Parts;

use App\Models\Orders\Parts\Item;
use Database\Factories\BaseFactory;
use Database\Factories\Inventories\InventoryFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\Parts\Item>
 */
class ItemFactory extends BaseFactory
{
    protected $model = Item::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'discount' => 0,
            'order_id' => OrderFactory::new(),
            'inventory_id' => InventoryFactory::new(),
            'qty' => 1,
        ];
    }
}
