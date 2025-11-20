<?php

namespace Database\Factories;

use App\Models\Translate;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslateFactory extends Factory
{
    protected $model = Translate::class;

    public function definition(): array
    {
        return [
            'alias' => $this->faker->unique()->word(15),
            'text' => $this->faker->sentence,
            'lang' => 'ru',
            'model' => Translate::TYPE_SITE,
        ];
    }
}
