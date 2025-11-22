<?php


namespace App\GraphQL\InputTypes\Inspection;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Tires\Tire;
use App\Models\Vehicles\Schemas\SchemaWheel;
use GraphQL\Type\Definition\Type;

class InspectionTireInputType extends BaseInputType
{
    public const NAME = 'InspectionTireInputType';

    public function fields(): array
    {
        return [
            'tire_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Tire::ruleExists()
                ]
            ],
            'schema_wheel_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    SchemaWheel::ruleExists()
                ]
            ],
            'ogp' => [
                'type' => NonNullType::float(),
            ],
            'pressure' => [
                'type' => NonNullType::float(),
            ],
            'comment' => [
                'type' => Type::string()
            ],
            'problems' => [
                'type' => Type::listOf(
                    NonNullType::id()
                )
            ],
            'recommendations' => [
                'type' => InspectionRecommendationInputType::list()
            ],
            'photos' => [
                'type' => Type::listOf(
                    InspectionTirePhotosInputType::type()
                )
            ]
        ];
    }
}
