<?php

namespace Database\Factories\Support\Supports;

use App\Models\Support\Supports\Support;
use App\Models\Support\Supports\SupportTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|SupportTranslation[]|SupportTranslation create(array $attributes = [])
 */
class SupportTranslationFactory extends BaseTranslationFactory
{
    protected $model = SupportTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Support::factory(),
            'description' => $this->faker->sentence,
            'short_description' => $this->faker->sentence,
            'working_time' => $this->faker->sentence,
            'video_link' => $this->faker->imageUrl,
        ];
    }
}
