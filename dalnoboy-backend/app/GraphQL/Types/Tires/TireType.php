<?php

namespace App\GraphQL\Types\Tires;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Dictionaries\TireRelationshipTypeType;
use App\GraphQL\Types\Dictionaries\TireSpecificationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Tires\Tire;

class TireType extends BaseType
{
    public const NAME = 'TireType';
    public const MODEL = Tire::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'serial_number' => [
                    'type' => NonNullType::string(),
                ],
                'ogp' => [
                    'type' => NonNullType::float(),
                ],
                'specification' => [
                    'type' => TireSpecificationType::nonNullType(),
                    'is_relation' => true,
                ],
                'relationship_type' => [
                    'type' => TireRelationshipTypeType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'relationshipType',
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
