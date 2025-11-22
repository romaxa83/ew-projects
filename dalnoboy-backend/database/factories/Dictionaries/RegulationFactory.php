<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\Regulation;
use App\Models\Dictionaries\RegulationTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Regulation|Regulation[]|Collection create(array $attributes = [])
 */
class RegulationFactory extends Factory
{
    protected $model = Regulation::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'days' => $this->faker->numerify,
            'distance' => $this->faker->numerify,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (Regulation $regulation) {
                foreach (languages() as $language) {
                    RegulationTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $regulation->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
