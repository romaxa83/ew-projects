<?php


namespace App\GraphQL\InputTypes\Inspection;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\Recommendation;
use App\Models\Tires\Tire;
use GraphQL\Type\Definition\Type;

class InspectionRecommendationInputType extends BaseInputType
{
    public const NAME = 'InspectionRecommendationInputType';

    public function fields(): array
    {
        return [
            'recommendation_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Recommendation::ruleExists()
                ]
            ],
            'is_confirmed' => [
                'type' => NonNullType::boolean(),
            ],
            'new_tire_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'int',
                    Tire::ruleExists()
                ]
            ]
        ];
    }
}
