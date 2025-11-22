<?php

namespace Database\Factories\Promotion;

use App\Models\Promotion\PromotionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromotionTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lang' => 'ru',
            'name' => $this->faker->title,
            'text' => $this->faker->paragraph,
        ];
    }
}
