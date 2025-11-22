<?php

namespace Database\Factories\TypeOfWorks;

use App\Models\TypeOfWorks\TypeOfWorkInventory;
use Database\Factories\BaseFactory;
use Database\Factories\Inventories\InventoryFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeOfWorks\TypeOfWorkInventory>
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
        ];
    }
}
