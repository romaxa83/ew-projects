<?php

namespace WezomCms\Catalog\Database\Factories\Specifications;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Specifications\Specification;

class SpecificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Specification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $data = [
            'published' => true,
            'sort' => $this->faker->randomNumber(),
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), [
            'name' => $this->faker->realText(15),
        ]));
    }
}
