<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionClimateZoneEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;

class FindSolutionType extends BaseType
{
    public const NAME = 'FindSolutionType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Solution ID (not product ID)',
            ],
            'climate_zones' => [
                'type' => NonNullType::listOf(
                    SolutionClimateZoneEnumType::nonNullType(),
                )
            ],
            'series' => [
                'type' => SolutionSeriesType::nonNullType(),
            ],
            'zone' => [
                'type' => SolutionZoneEnumType::nonNullType(),
            ],
            'btu' => [
                'type' => NonNullType::int(),
            ],
            'voltage' => [
                'type' => NonNullType::int(),
            ],
            'product' => [
                'type' => ProductType::nonNullType(),
            ],
            'is_correct_btu' => [
                'type' => NonNullType::boolean(),
                'description' => 'If all indoors BTU is correct to outdoor BTU It will be true.'
            ],
            'indoors' => [
                'type' => FindSolutionIndoorType::nonNullList(),
            ]
        ];
    }
}
