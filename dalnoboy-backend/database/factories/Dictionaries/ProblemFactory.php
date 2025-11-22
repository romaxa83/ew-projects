<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\ProblemTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Problem|Problem[]|Collection create(array $attributes = [])
 */
class ProblemFactory extends Factory
{
    protected $model = Problem::class;

    public function definition(): array
    {
        return [
            'active' => true,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (Problem $problem) {
                foreach (languages() as $language) {
                    ProblemTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $problem->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
