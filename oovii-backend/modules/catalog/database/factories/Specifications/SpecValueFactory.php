<?php

namespace WezomCms\Catalog\Database\Factories\Specifications;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue;

class SpecValueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpecValue::class;

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
            'specification_id' => Specification::inRandomOrder()->first()->id,
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), [
            'name' => $this->faker->realText(15),
        ]));
    }
}
