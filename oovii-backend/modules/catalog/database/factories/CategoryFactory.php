<?php

namespace WezomCms\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->realText(15);

        $parent = Category::published()
            ->whereNull('parent_id')
            ->inRandomOrder()
            ->first();

        $data = [
            'published' => true,
            'parent_id' => $parent ? $parent->id : null,
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), [
            'name' => $name,
            'text' => $this->faker->realText(50),
            'title' => $name,
            'h1' => $name,
        ]));
    }
}
