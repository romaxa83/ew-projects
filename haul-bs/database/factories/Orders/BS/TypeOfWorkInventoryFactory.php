<?php

namespace Database\Factories\Orders\BS;

use App\Models\Orders\BS\TypeOfWorkInventory;
use Database\Factories\BaseFactory;
use Database\Factories\Inventories\InventoryFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\BS\TypeOfWorkInventory>
 */
class TypeOfWorkInventoryFactory extends BaseFactory
{
    protected $model = TypeOfWorkInventory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type_of_work_id' => TypeOfWorkFactory::new(),
            'inventory_id' => InventoryFactory::new(),
            'quantity' => 10.9,
            'price' => 12.5,
        ];
    }
}
