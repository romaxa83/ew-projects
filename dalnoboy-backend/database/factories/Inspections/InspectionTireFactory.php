<?php

namespace Database\Factories\Inspections;

use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Inspections\InspectionTire;
use App\Models\Tires\Tire;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|InspectionTire[]|InspectionTire create(array $attributes = [])
 */
class InspectionTireFactory extends Factory
{
    protected $model = InspectionTire::class;

    public function definition(): array
    {
        $tire = Tire::factory()
            ->create();

        return [
            'tire_id' => $tire->id,
            'ogp' => $tire->ogp - 0.2,
            'pressure' => 2.2,
            'comment' => $this->faker->text,
            'no_problems' => true
        ];
    }

    public function withProblems(): self
    {
        return $this
            ->state(
                [
                    'no_problems' => false
                ]
            )
            ->hasAttached(
                Problem::factory()
                    ->count(5),
                relationship: 'problems'
            );
    }

    public function withRecommendations(): self
    {
        return $this
            ->hasAttached(
                Recommendation::factory()
                    ->count(5),
                [
                    'is_confirmed' => $this->faker->boolean,
                    'new_tire_id' => $this->faker->boolean ? Tire::factory()
                        ->create()->id : null
                ],
                'recommendations'
            );
    }
}
