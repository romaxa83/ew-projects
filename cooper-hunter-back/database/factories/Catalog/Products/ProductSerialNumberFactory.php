<?php

namespace Database\Factories\Catalog\Products;

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method ProductSerialNumber|ProductSerialNumber[]|Collection create(array $attrs = [])
 */
class ProductSerialNumberFactory extends BaseFactory
{
    protected $model = ProductSerialNumber::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'serial_number' => strtoupper($this->faker->unique()->bothify('???########'))
        ];
    }
}
