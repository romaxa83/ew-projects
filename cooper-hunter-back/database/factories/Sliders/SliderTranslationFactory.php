<?php

namespace Database\Factories\Sliders;

use App\Models\Sliders\Slider;
use App\Models\Sliders\SliderTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|SliderTranslation[]|SliderTranslation create(array $attributes = [])
 */
class SliderTranslationFactory extends BaseTranslationFactory
{
    protected $model = SliderTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Slider::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
        ];
    }
}
