<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireSpecification;

class TireSpecificationType extends BaseType
{
    public const NAME = 'TireSpecificationType';
    public const MODEL = TireSpecification::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'make' => [
                    'type' => TireMakeType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'tireMake',
                ],
                'model' => [
                    'type' => TireModelType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'tireModel',
                ],
                'type' => [
                    'type' => TireTypeType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'tireType',
                ],
                'size' => [
                    'type' => TireSizeType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'tireSize',
                ],
                'ngp' => [
                    'type' => NonNullType::float(),
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
