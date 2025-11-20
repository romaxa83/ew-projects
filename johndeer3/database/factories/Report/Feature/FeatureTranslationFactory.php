<?php

namespace Database\Factories\Report\Feature;

use App\Models\Report\Feature\FeatureTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureTranslationFactory extends Factory
{
    protected $model = FeatureTranslation::class;

    public function definition(): array
    {
        return [
            'lang' => \App::getLocale(),
            'name' => $this->faker->word,
            'unit' => $this->faker->word,
        ];
    }
}


