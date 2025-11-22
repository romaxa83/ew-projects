<?php

namespace Database\Factories\Inventories;

use App\Models\Inventories\Brand;
use Database\Factories\BaseFactory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Brand>
 */
class BrandFactory extends BaseFactory
{
    protected $model = Brand::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->city . random_int(1000, 20000) . fake()->streetName;

        return [
            'name' => $name,
            'slug' => Str::slug($name)
        ];
    }
}
