<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionClimateZoneEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionTypeEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Solution;
use App\Models\Catalog\Solutions\SolutionDefaultLineSet;
use GraphQL\Type\Definition\Type;

class SolutionType extends BaseType
{
    public const NAME = 'SolutionType';
    public const MODEL = Solution::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Solution setting ID',
            ],
            'product' => [
                'type' => ProductType::nonNullType(),
                'is_relation' => true
            ],
            'type' => [
                'type' => SolutionTypeEnumType::nonNullType(),
            ],
            'short_name' => [
                'type' => Type::string(),
                'is_relation' => true,
                'description' => 'Only LINE_SET',
            ],
            'series' => [
                'type' => SolutionSeriesType::type(),
                'is_relation' => true,
                'description' => 'Only OUTDOOR/INDOOR',
            ],
            'zone' => [
                'type' => SolutionZoneEnumType::type(),
                'description' => 'Only OUTDOOR',
            ],
            'climate_zones' => [
                'type' => Type::listOf(
                    SolutionClimateZoneEnumType::nonNullType()
                ),
                'description' => 'Only OUTDOOR',
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Solution $solution) => $solution->type->is(SolutionTypeEnum::OUTDOOR) ?
                    $solution->climateZones->pluck('climate_zone')
                        ->toArray() :
                    null,
            ],
            'indoor_type' => [
                'type' => SolutionIndoorEnumType::type(),
                'description' => 'Only INDOOR'
            ],
            'btu' => [
                'type' => Type::int(),
                'description' => 'Only INDOOR/OUTDOOR',
            ],
            'max_btu_percent' => [
                'type' => Type::int(),
                'description' => 'Only OUTDOOR'
            ],
            'voltage' => [
                'type' => Type::int(),
                'description' => 'Only OUTDOOR - 115/230 V',
            ],
            'default_schemas' => [
                'type' => SolutionDefaultSchemaType::list(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => function (Solution $solution)
                {
                    if ($solution->type->isNot(SolutionTypeEnum::OUTDOOR) || $solution->zone->isNot(
                            SolutionZoneEnum::MULTI
                        )) {
                        return null;
                    }
                    $schemas = $solution
                        ->schemas()
                        ->with('indoor')
                        ->orderBy('count_zones')
                        ->orderBy('zone')
                        ->get();

                    $result = [];

                    foreach ($schemas as $schema) {
                        $result[$schema->count_zones]['count_zones'] = $schema->count_zones;
                        $result[$schema->count_zones]['indoors'][] = $schema->indoor;
                    }
                    return $result;
                }
            ],
            'line_sets' => [
                'type' => SolutionLineSetType::list(),
                'description' => 'Connected line sets to indoor',
                'resolve' => function (Solution $solution)
                {
                    if ($solution->type->isNot(SolutionTypeEnum::INDOOR)) {
                        return null;
                    }

                    $defaultLineSet = [];

                    $solution
                        ->defaultLineSets
                        ->each(
                            function (SolutionDefaultLineSet $item) use (&$defaultLineSet)
                            {
                                $defaultLineSet[$item->line_set_id][] = $item->zone;
                            }
                        );

                    return $solution->children->map(
                        fn(Solution $lineSet) => [
                            'line_set' => $lineSet,
                            'default_for_zones' => $defaultLineSet[$lineSet->id] ?? null
                        ]
                    );
                },
                'is_relation' => false,
                'selectable' => false,
            ],
            'indoors' => [
                'type' => SolutionType::list(),
                'description' => 'Connected indoors to outdoor',
                'resolve' => fn(Solution $solution) => $solution
                    ->type
                    ->is(SolutionTypeEnum::OUTDOOR) ?
                    $solution->children : null,
                'is_relation' => false,
                'selectable' => false,
            ]
        ];
    }
}
