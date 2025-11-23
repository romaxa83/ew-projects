<?php

namespace WezomCms\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Core\Models\Setting;

class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $data = [
            'module' => $this->faker->word,
            'group' => $this->faker->word,
            'key' => $this->faker->word,
            'type' => 'input',
            'image_settings' => [],
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), [
            'value' => $this->faker->sentence,
        ]));
    }
}
