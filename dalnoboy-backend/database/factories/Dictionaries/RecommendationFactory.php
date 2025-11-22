<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\Recommendation;
use App\Models\Dictionaries\RecommendationTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Recommendation|Recommendation[]|Collection create(array $attributes = [])
 */
class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    public function definition(): array
    {
        return [
            'active' => true,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (Recommendation $recommendation) {
                foreach (languages() as $language) {
                    RecommendationTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $recommendation->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
