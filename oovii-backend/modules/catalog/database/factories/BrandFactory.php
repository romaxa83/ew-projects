<?php

namespace WezomCms\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Brand;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $data = [
            'published' => $this->faker->boolean(80),
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), ['name' => $this->faker->realText(15)]));
    }

    /**
     * Add a new state transformation to the model definition.
     *
     * @return Factory
     */
    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'published' => true
            ];
        });
    }
}
