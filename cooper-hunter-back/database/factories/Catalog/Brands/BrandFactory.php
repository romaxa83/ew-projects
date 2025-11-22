<?php

namespace Database\Factories\Catalog\Brands;

use App\Models\Catalog\Brands\Brand;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @method Brand|Brand[]|Collection create(array $attrs = [])
 */
class BrandFactory extends BaseFactory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->company;
        return [
            'name' => $name,
            'slug' => Str::slug($name)
        ];
    }
}
