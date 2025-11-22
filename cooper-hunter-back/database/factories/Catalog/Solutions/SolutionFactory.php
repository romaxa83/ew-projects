<?php

namespace Database\Factories\Catalog\Solutions;

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Models\Catalog\Solutions\Solution;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Solution|Solution[]|Collection create(array $attrs = [])
 */
class SolutionFactory extends BaseFactory
{
    protected $model = Solution::class;

    private ?Collection $children = null;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory()
        ];
    }

    public function children(Collection $children): static
    {
        $this->children = $children;

        return $this;
    }

    public function schemas(): static
    {
        return $this;
    }

    public function outdoor(): static
    {
        $children = $this->children;

        $this->children = null;

        return $this->afterCreating(
            function (Solution $solution) use ($children)
            {
                $children = !$children ? Solution::factory()
                    ->indoor()
                    ->count(2)
                    ->create() :
                    $children;

                $solution
                    ->children()
                    ->sync($children);

                $solution
                    ->climateZones()
                    ->createMany(
                        [
                            [
                                'climate_zone' => SolutionClimateZoneEnum::COLD,
                            ],
                            [
                                'climate_zone' => SolutionClimateZoneEnum::MODERATE,
                            ],
                        ]
                    );

                if ($solution->zone === SolutionZoneEnum::SINGLE) {
                    return;
                }
                $solution->schemas()
                    ->createMany(
                        [
                            [
                                'indoor_id' => $children[0]->id,
                                'zone' => 1,
                                'count_zones' => 2
                            ],
                            [
                                'indoor_id' => $children[0]->id,
                                'zone' => 2,
                                'count_zones' => 2
                            ],
                        ]
                    );
            }
        )
            ->state(
                [
                    'type' => SolutionTypeEnum::OUTDOOR,
                    'zone' => SolutionZoneEnum::MULTI,
                    'btu' => config(
                        'catalog.solutions.btu.lists.' .
                        SolutionTypeEnum::OUTDOOR . '.' .
                        SolutionZoneEnum::MULTI
                    )[2],
                    'voltage' => config('catalog.solutions.voltage.default'),
                    'series_id' => SolutionSeries::query()
                        ->first()->id
                ]
            );
    }

    public function indoor(): static
    {
        $children = $this->children;

        $this->children = null;

        return $this->afterCreating(
            function (Solution $solution) use ($children)
            {
                $children = !$children ? Solution::factory()
                    ->lineSet()
                    ->count(2)
                    ->create() :
                    $children;

                $solution
                    ->children()
                    ->sync(
                        $children
                    );
                $solution
                    ->defaultLineSets()
                    ->createMany(
                        [
                            [
                                'line_set_id' => $children[0]->id,
                                'zone' => SolutionZoneEnum::MULTI()
                            ],
                            [
                                'line_set_id' => $children[0]->id,
                                'zone' => SolutionZoneEnum::SINGLE()
                            ],
                        ]
                    );
            }
        )
            ->state(
                [
                    'type' => SolutionTypeEnum::INDOOR,
                    'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
                    'btu' => config(
                        'catalog.solutions.btu.lists.' .
                        SolutionTypeEnum::INDOOR . '.' .
                        SolutionZoneEnum::MULTI . '.' .
                        SolutionIndoorEnum::WALL_MOUNT
                    )[3],
                    'series_id' => SolutionSeries::query()
                        ->first()->id
                ]
            );
    }

    public function lineSet(): static
    {
        return $this->state(
            [
                'type' => SolutionTypeEnum::LINE_SET,
                'short_name' => $this->faker->lexify,
            ]
        );
    }
}
