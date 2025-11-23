<?php

namespace WezomCms\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\ProductSpecification;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue;

class ProductSpecificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductSpecification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws \Exception
     */
    public function definition()
    {
        /** @var Product $product */
        $product = Product::inRandomOrder()->first();

        /** @var Specification $specification */
        $specification = Specification::inRandomOrder()->firstOrNew();

        /** @var SpecValue $specValue */
        $specValue = $specification->specValues()->inRandomOrder()->first();

        if (!$product || !$specValue) {
            throw new \Exception('Not enough data to create relations');
        }

        return [
            'product_id' => $product->id,
            'spec_id' => $specification->id,
            'spec_value_id' => $specValue->id
        ];
    }
}
