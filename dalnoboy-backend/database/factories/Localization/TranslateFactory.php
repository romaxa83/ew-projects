<?php

namespace Database\Factories\Localization;

use App\Models\Localization\Translate;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslateFactory extends Factory
{
    protected $model = Translate::class;

    public function definition(): array
    {
        return [
            'place' => $this->faker->unique()->bothify,
            'key' => $this->faker->unique()->bothify,
            'text' => $this->faker->sentence,
            'lang' => $this->faker->language,
        ];
    }
}
