<?php

namespace App\GraphQL\Types\Catalog\Features\Specifications;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Specification;

class SpecificationType extends BaseType
{
    public const NAME = 'SpecificationType';
    public const MODEL = Specification::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'icon' => [
                'type' => NonNullType::string(),
            ],
            'translation' => [
                'type' => SpecificationTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => SpecificationTranslationType::nonNullList(),
            ],
        ];
    }
}
