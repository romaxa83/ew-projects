<?php

namespace Database\Factories\Inventories;

use App\Enums\Inventories\InventoryPackageType;
use App\Models\Inventories\Inventory;
use Database\Factories\BaseFactory;
use Database\Factories\Suppliers\SupplierFactory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Inventory>
 */
class InventoryFactory extends BaseFactory
{
    protected $model = Inventory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->city . random_int(1000, 20000) . fake()->streetName;
        return [
            'category_id' => CategoryFactory::new(),
            'unit_id' => UnitFactory::new(),
            'supplier_id' => SupplierFactory::new(),
            'brand_id' => BrandFactory::new(),
            'active' => true,
            'name' => $name,
            'slug' => Str::slug($name),
            'notes' => fake()->sentence,
            'price_retail' => fake()->randomFloat(2, 1, 1000),
            'min_limit_price' => fake()->randomFloat(2, 1, 1000),
            'quantity' => fake()->numberBetween(20, 40),
            'min_limit' => 5,
            'stock_number' => fake()->word .'-'. fake()->word .'_'. random_int(1000, 20000),
            'article_number' => fake()->word .'-'. fake()->word .'_'. random_int(1000, 20000),
            'for_shop' => true,
            'length' => fake()->randomFloat(2, 1, 1000),
            'width' => fake()->randomFloat(2, 1, 1000),
            'height' => fake()->randomFloat(2, 1, 1000),
            'weight' => fake()->randomFloat(2, 1, 100),
            'origin_id' => null,
            'is_new' => false,
            'is_popular' => false,
            'is_sale' => false,
            'discount' => null,
            'old_price' => null,
            'delivery_cost' => null,
            'package_type' => InventoryPackageType::Carrier->value,
        ];
    }
}
