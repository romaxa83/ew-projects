<?php

namespace Database\Factories\Inventories;

use App\Models\Inventories\Category;
use Database\Factories\BaseFactory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Category>
 */
class CategoryFactory extends BaseFactory
{
    protected $model = Category::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->city . random_int(1000, 20000) . fake()->streetName;

        return [
            'active' => true,
            'name' => $name,
            'slug' => Str::slug($name),
            'desc' => fake()->sentence,
            'position' => 1,
            'parent_id' => null,
            'origin_id' => null,
            'display_menu' => false,
        ];
    }
}
