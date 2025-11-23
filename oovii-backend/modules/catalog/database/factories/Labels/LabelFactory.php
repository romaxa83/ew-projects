<?php

namespace WezomCms\Catalog\Database\Factories\Labels;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Labels\Label;

class LabelFactory extends Factory
{
    protected $model = Label::class;

    public function definition(): array
    {
        $data = [
            'published' => $this->faker->boolean(80),
            'is_gender' => false,
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), [
            'name' => $this->faker->city
        ]));
    }
}

