<?php

namespace Database\Factories\Catalog\Products;

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductKeyword;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ProductKeyword[]|ProductKeyword create(array $attributes = [])
 */
class ProductKeywordFactory extends BaseFactory
{
    protected $model = ProductKeyword::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'keyword' => $this->faker->word,
        ];
    }
}
