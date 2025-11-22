<?php

namespace Database\Factories\Inventories;

use App\Models\Inventories\InventoryFeature;
use Database\Factories\BaseFactory;
use Database\Factories\Inventories\Features\FeatureFactory;
use Database\Factories\Inventories\Features\ValueFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\InventoryFeature>
 */
class InventoryFeatureFactory extends BaseFactory
{
    protected $model = InventoryFeature::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'inventory_id' => InventoryFactory::new(),
            'feature_id' => FeatureFactory::new(),
            'value_id' => ValueFactory::new(),
        ];
    }
}
