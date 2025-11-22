<?php

namespace App\GraphQL\Types\Inspections;

use App\GraphQL\Types\Dictionaries\RecommendationType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Tires\TireType;
use App\Models\Dictionaries\Recommendation;

class InspectionRecommendationType extends RecommendationType
{
    public const NAME = 'InspectionRecommendationType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'is_confirmed' => [
                    'type' => NonNullType::boolean(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Recommendation $recommendation) => $recommendation->pivot->is_confirmed
                ],
                'new_tire' => [
                    'type' => TireType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Recommendation $recommendation) => $recommendation->pivot->tire
                ]
            ]
        );
    }
}
