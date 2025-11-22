<?php

namespace App\GraphQL\Types\Inspections;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Dictionaries\ProblemType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Tires\TireType;
use App\GraphQL\Types\Vehicles\Schemas\SchemaWheelType;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use GraphQL\Type\Definition\Type;

class InspectionTireType extends BaseType
{
    public const NAME = 'InspectionTireType';
    public const MODEL = InspectionTire::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'tire' => [
                'type' => TireType::nonNullType(),
                'is_relation' => true,
            ],
            'schema_wheel' => [
                'type' => SchemaWheelType::nonNullType(),
                'is_relation' => true,
                'alias' => 'schemaWheel'
            ],
            'ogp' => [
                'type' => NonNullType::float(),
            ],
            'pressure' => [
                'type' => NonNullType::float()
            ],
            'comment' => [
                'type' => Type::string(),
            ],
            'no_problems' => [
                'type' => NonNullType::boolean(),
            ],
            'problems' => [
                'type' => ProblemType::list(),
                'is_relation' => true,
            ],
            'recommendations' => [
                'type' => InspectionRecommendationType::list(),
                'is_relation' => true,
            ],
            'previous_inspection_ogp' => [
                'type' => Type::float(),
                'is_relation' => false,
                'selectable' => false,
                'always' => ['tire_id'],
                'resolve' => fn(InspectionTire $inspection) => $inspection->previousTireInspection()?->ogp,
            ],
            'photos' => [
                'type' => InspectionTirePhotosType::nonNullType(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(InspectionTire $m) => $m
            ],
        ];
    }
}
