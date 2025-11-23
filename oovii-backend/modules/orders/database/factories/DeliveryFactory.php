<?php

namespace WezomCms\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelLocalization;
use WezomCms\Orders\Models\Delivery;

class DeliveryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Delivery::class;

    public function definition(): array
    {
        $data = [
            'sort' => $this->faker->numberBetween(0, 127),
        ];

        $name = $this->faker->words(3, true);

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
            $data[$lang] = [
                'name' => $name . ' - ' . $lang,
            ];
        }

        return $data;
    }
}
