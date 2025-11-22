<?php

namespace App\GraphQL\InputTypes\Catalog\Solutions;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionClimateZoneEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionTypeEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class FindSolutionInputType extends BaseInputType
{
    public const NAME = 'FindSolutionInputType';

    public function fields(): array
    {
        return [
            'product_id' => [
                'type' => NonNullType::id(),
            ],
            'type' => [
                'type' => SolutionTypeEnumType::nonNullType(),
            ],
            'short_name' => [
                'type' => Type::string(),
                'description' => 'Required if type is LINE_SET',
            ],
            'series_id' => [
                'type' => Type::id(),
                'description' => 'Required if type is OUTDOOR/INDOOR',
            ],
            'zone' => [
                'type' => SolutionZoneEnumType::type(),
                'description' => 'Required if type is OUTDOOR',
            ],
            'climate_zones' => [
                'type' => Type::listOf(
                    SolutionClimateZoneEnumType::nonNullType()
                ),
                'description' => 'Required if type is OUTDOOR',
            ],
            'indoor_type' => [
                'type' => SolutionIndoorEnumType::type(),
                'description' => 'Required if type is INDOOR',
            ],
            'max_btu_percent' => [
                'type' => Type::int(),
                'defaultValue' => config('catalog.solutions.btu.max_percent'),
            ],
            'btu' => [
                'type' => Type::int(),
                'description' => 'Required if type is INDOOR/OUTDOOR',
            ],
            'voltage' => [
                'type' => Type::int(),
                'description' => 'Required if type is OUTDOOR - 115/230 V',
            ],
            'line_sets' => [
                'type' => FindSolutionLineSetInputType::list(),
                'description' => 'Required if type is INDOOR. Array of solution.id with type LINE_SET'
            ],
            'default_schemas' => [
                'type' => FindSolutionDefaultSchemaInputType::list(),
                'description' => 'Description for default schemas. Required for multi outdoor',
            ],
            'indoors' => [
                'type' => Type::listOf(
                    NonNullType::id()
                ),
                'description' => 'Required if type is OUTDOOR'
            ]
        ];
    }
}
