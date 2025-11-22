<?php

namespace Database\Factories\Companies;

use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Companies\Price;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Price|Price[]|Collection create(array $attributes = [])
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'price' => random_int(100, 1000),
            'desc' => $this->faker->sentence,
        ];
    }
}
