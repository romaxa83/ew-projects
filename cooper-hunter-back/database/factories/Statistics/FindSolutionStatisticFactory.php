<?php

namespace Database\Factories\Statistics;

use App\Collections\Statistics\Solutions\IndoorsCollection;
use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionSeriesEnum;
use App\Models\Statistics\FindSolutionStatistic;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;
use JsonException;

/**
 * @method Collection|FindSolutionStatistic[]|FindSolutionStatistic create($attributes = [])
 */
class FindSolutionStatisticFactory extends BaseFactory
{
    public $model = FindSolutionStatistic::class;

    /**
     * @throws JsonException
     */
    public function definition(): array
    {
        return [
            'outdoor' => $this->faker->bothify('??-?????-??###??'),
            'outdoor_btu' => $this->faker->numerify('#####'),
            'outdoor_voltage' => $this->faker->randomElement([115, 230]),
            'climate_zone' => $this->faker->randomElement(SolutionClimateZoneEnum::getValues()),
            'series' => $this->faker->randomElement(SolutionSeriesEnum::getValues()),
            'indoors' => IndoorsCollection::resolve([
                [
                    'unit' => $this->faker->bothify('??-?????-??###??'),
                    'type' => $this->faker->randomElement(SolutionIndoorEnum::getValues()),
                    'btu' => $this->faker->numerify('#####'),
                    'line_set' => $this->faker->bothify('???-###-???'),
                ]
            ]),
        ];
    }

    public function indoorsCount(int $count): self
    {
        $indoor = fn(): array => [
            'unit' => $this->faker->bothify('??-?????-??###??'),
            'type' => $this->faker->randomElement(SolutionIndoorEnum::getValues()),
            'btu' => $this->faker->numerify('#####'),
            'line_set' => $this->faker->bothify('???-###-???'),
        ];

        $indoors = [];

        foreach (range(1, $count) as $ignored) {
            $indoors[] = $indoor();
        }

        $indoors = IndoorsCollection::resolve($indoors);

        return $this->state(compact('indoors'));
    }
}