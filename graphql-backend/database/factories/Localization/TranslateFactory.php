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
            'place' => $this->faker->unique()->word,
            'key' => $this->faker->unique()->word,
            'text' => $this->faker->sentence,
            'lang' => app('localization')->getDefaultSlug(),
        ];
    }
}
