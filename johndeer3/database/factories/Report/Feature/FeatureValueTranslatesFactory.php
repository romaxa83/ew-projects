<?php

namespace Database\Factories\Report\Feature;

use App\Models\Report\Feature\FeatureValueTranslates;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureValueTranslatesFactory extends Factory
{
    protected $model = FeatureValueTranslates::class;

    public function definition(): array
    {
        return [
            'lang' => \App::getLocale(),
            'name' => $this->faker->word,
        ];
    }
}




