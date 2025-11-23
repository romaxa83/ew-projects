<?php

namespace WezomCms\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Model;

class ModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Model::class;

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
}
